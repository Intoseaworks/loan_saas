<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\User;

use Admin\Models\Collection\Collection;
use Admin\Models\Order\Order;
use Admin\Models\Upload\Upload;
use Admin\Models\User\User;
use Admin\Models\User\UserBlack;
use Admin\Services\BaseService;
use Admin\Services\Data\DataServer;
use Admin\Services\Risk\RiskServer;
use Admin\Services\Upload\UploadServer;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;
use Common\Validators\Validation;

class UserBlackServer extends BaseService
{
    private $user;

    /**
     * @param null $userId
     * @throws \Common\Exceptions\ApiException
     */
    public function __construct($userId = null)
    {
        if ($userId !== null && !$this->user = User::model()->getOne($userId)) {
            $this->outputException('订单数据不存在');
        }
    }

    public function add($telephone, $param)
    {
        $param['telephone'] = $telephone;
        $param['merchant_id'] = $param['merchant_id'] ?? MerchantHelper::getMerchantId();
        return UserBlack::model()->add($param);
    }

    public function move($id)
    {
        $userBlack = UserBlack::model()->getOne($id);
        if (!$userBlack) {
            return $this->outputException('黑名单不存在');
        }
        if ($userBlack->status != UserBlack::STATUS_NORMAL) {
            return $this->outputException('黑名单已移除');
        }
        return UserBlack::model()->move($userBlack->telephone);
    }

    /**
     * 获取列表
     * @param $param
     * @return mixed
     */
    public function getList($param)
    {
        $datas = UserBlack::model()->getList($param) ?? [];
        foreach ($datas as $data) {
            $data->type = t(array_get(UserBlack::TPYE, $data->type), 'user');
            if (!$data->user) {
                continue;
            }
            $data->user->setScenario(User::SCENARIO_BLACK)->getText();
            $data->user->channel && $data->user->channel->getText(['channel_code', 'channel_name']);
            $data->user->userInfo && $data->user->userInfo->getText(['email']);
            if ($data->user->order) {
                $data->user->order->setScenario(Order::SCENARIO_LIST)->getText();
                $data->user->order->collection && $data->user->order->collection->setScenario(Collection::SCENARIO_SIMPLE)->getText();
            }
        }
        return $datas;
    }

    public function view()
    {
        $tabs = [
            DataServer::ORDER,
            DataServer::USER,
//            RiskServer::OPERATOR_REPORT,
            DataServer::BANK_CARDS,
//            RiskServer::USER_POSITION,
//            RiskServer::CONTACTS,
            DataServer::ORDER_LIST,
//            RiskServer::USER_SMS,
            RiskServer::USER_APP,
        ];
        $lastOrderId = $this->user->order->id ?? null;
        return DataServer::server($this->user->id, $lastOrderId)->list($tabs);
    }

    /**
     * 用户黑名单确认提交
     * @param $request
     * @return bool
     * @throws \Common\Exceptions\ApiException
     */
    public function confirm($request)
    {
        $sourceId = $request->input('source_id');//upload_id
        $type = $request->input('type', '');
        $black_time = $request->input('black_time', date('Y-m-d'));

        /**
         * 下载
         */
        //$paths = UploadServer::getPathsBySourceIdAndType($sourceId, [Upload::TYPE_USER_BLACK_LIST])->toArray();
        $upload = Upload::model()->geyById($sourceId);
        if (!$upload) {
            return $this->outputException('对应文件不存在');
        }
        if ($upload->source_id != LoginHelper::getAdminId() || $upload->type != Upload::TYPE_USER_BLACK_LIST) {
            return $this->outputException('对应文件非法');
        }
        $paths = (array)$upload->path;

        $tmpFileName = UploadServer::setDownloadTmpFileName($paths);
        UploadServer::resetDownloadTmp();

        $fileName = UploadServer::getFileName($sourceId, $type);

        if (!file_exists($tmpFileName)) {
            if (!$downloadFile = UploadServer::downloadFiles($paths, $tmpFileName)) {
                return $this->outputException('下载失败');
            }
        }

        UploadServer::download($tmpFileName, false, $fileName);

        /**
         * 保存数据
         */
        $row = 0;
        if (($handle = fopen($tmpFileName, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $num = count($data);
                $row++;
                for ($c = 0; $c < $num; $c++) {
                    if (Validation::validateMobile('', $data[$c])) {
                        UserBlack::model()->add([
                            'telephone' => $data[$c],
                            'black_time' => $black_time,
                            'type' => $type,
                            'merchant_id' => MerchantHelper::getMerchantId(),
                        ]);
                        User::model()->where('telephone', $data[$c])->update(['status' => User::STATUS_DISABLE]);
                    }
                }
            }
            fclose($handle);
        } else {
            return $this->outputException('文件打开异常');
        }

        return true;
    }

    /**
     * 上传用户黑名单
     * @param $request
     * @return UserServer|array
     * @throws \Exception
     */
    public function uploadCsv($request)
    {
        $path = $request->file('file')->store('file', 'local');
        $adminId = LoginHelper::getAdminId();
        $row = 0;
        if (($handle = fopen(storage_path('app/' . $path), "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $num = count($data);
                for ($c = 0; $c < $num; $c++) {
                    if (Validation::validateMobile('', $data[$c])) {
                        $row++;
                    }
                }
            }
            fclose($handle);
        }

        if (!$attributes = UploadServer::moveFile($request->file('file'))) {
            return $this->outputException('上传文件保存失败');
        }

        $attributes['type'] = Upload::TYPE_USER_BLACK_LIST;
        $attributes['user_id'] = $adminId;
        $attributes['source_id'] = $adminId;
        if (!$model = Upload::model(Upload::SCENARIO_CREATE)->saveModel($attributes)) {
            return $this->outputException('上传文件保存记录失败');
        }

        return ['num' => $row, 'source_id' => $model->id];
    }
}
