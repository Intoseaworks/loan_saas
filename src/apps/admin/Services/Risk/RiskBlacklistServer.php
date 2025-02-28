<?php


namespace Admin\Services\Risk;


use Common\Models\Order\Order;
use Common\Models\Risk\RiskBlacklist;
use Common\Models\Upload\Upload;
use Common\Utils\Export\LaravelExcel\importTrait;
use Common\Utils\Upload\ImageHelper;
use Illuminate\Http\Request;

class RiskBlacklistServer extends \Common\Services\Risk\RiskBlacklistServer
{
    use importTrait;

    public function list(array $getParams)
    {
        $query = RiskBlacklist::model()->search($getParams);
        $size = array_get($getParams, 'size');
        $dataS = $query->paginate($size);
        foreach ($dataS as $data) {
            $data->getText();
        }
        return $dataS;
    }

    public function detail($id)
    {
        /** @var RiskBlacklist $blacklist */
        $blacklist = RiskBlacklist::find($id);
        /** @var Order $order */
        $order = $blacklist->order;
        $user = $order->user;
        $userInfo = $user->userInfo;
        $bankCard = $user->bankCard;
        $upload = (new \Admin\Models\Upload\Upload())->getPathsByUserIdAndType($order->user->id, [
            Upload::TYPE_BUK_NIC,
            Upload::TYPE_BUK_PASSPORT,
        ]);
        $fileUrls = [];
        $imgRelation = array_flip(Upload::TYPE_BUK);
        foreach ($upload as $k => $v) {
            $fileUrls[$k] = ImageHelper::getPicUrl($v, 400);
        }
        $faceFile = array_get($fileUrls, Upload::TYPE_FACES);
        $companyIdFile = array_get($fileUrls, Upload::TYPE_COMPANY_ID);
        $idCardFile = array_get($fileUrls, $imgRelation[$user->card_type]);
        $blacklist->id_card = $idCardFile;
        $blacklist->user_face = $faceFile;
        $blacklist->company_id = $companyIdFile;
        $blacklist->fullname = $user->fullname;
        $blacklist->birthday = $userInfo->birthday ?? '---';
        $blacklist->id_card_type = $user->card_type;
        $blacklist->id_card_no = $user->id_card_no;
        $blacklist->telephone = $user->telephone;
        $blacklist->work_phone = $user->userWork->work_phone ?? '---';
        $blacklist->email = $userInfo->email;
        foreach ($user->userContacts as $userContact) {
            $userContact->getText(['contact_fullname', 'contact_telephone', 'relation']);
        }
        $blacklist->contact_telephone = $user->userContacts;
        $blacklist->bank_name = $bankCard->bank_name;
        $blacklist->bank_account = $bankCard->account_no;
        foreach ($user->userPhoneHardwares as $userPhoneHardware) {
            $userPhoneHardware->getText(['imei', 'cookie_id', 'advertising_id', 'persistent_device_id', 'wifi_ip']);
        }
        $blacklist->hardwards = $user->userPhoneHardwares;
        unset($blacklist->order);
        return $blacklist;
    }

    /**
     * 线下导入数据处理
     * @param Request $request
     */
    public function import(Request $request)
    {
        $importData = $this->importExcel($request->file('file'));
        $success = 0;
        $count = count($importData);
        foreach ($importData as $data) {
            $this->checkoutFormat($data);
            $success += $this->manualAddBlack($data);
        }
        return $this->outputSuccess("上传{$count}条，成功导入{$success}条");
    }

    /**
     * 检查导入黑名单excel格式
     * @param $data
     * @return RiskBlacklistServer
     */
    private function checkoutFormat($data)
    {
        list($keyword, $value, $blackReason, $merchantId, $isGlobal) = $data;
        $keywords = array_keys(RiskBlacklist::KEYWORD_ALIAS);
        $blackReasons = array_keys(RiskBlacklist::TYPE_ALIAS);
        $isGlobals = [RiskBlacklist::IS_GLOBAL_YES, RiskBlacklist::IS_GLOBAL_NO];
        if (!in_array($keyword, $keywords)) {
            return $this->outputException('keyword不合法 请使用' . implode('|', $keywords));
        }
        if (!in_array($blackReason, $blackReasons)) {
            return $this->outputException('black_reason不合法 请使用' . implode('|', $blackReasons));
        }
        if (!in_array($isGlobal, $isGlobals)) {
            return $this->outputException('is_global不合法 请使用' . implode('|', $isGlobals));
        }
        if ($isGlobal == RiskBlacklist::IS_GLOBAL_NO && empty($merchantId)) {
            return $this->outputException('is_global为N时，merchant_id必填');
        }
        if (empty($value)) {
            return $this->outputException('value不能存在空值');
        }
    }
}
