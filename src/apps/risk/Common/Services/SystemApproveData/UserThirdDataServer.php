<?php

namespace Risk\Common\Services\SystemApproveData;

use Common\Services\BaseService;
use Common\Utils\Data\StringHelper;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\MerchantHelper;
use Risk\Common\Models\Business\User\UserThirdData;

class UserThirdDataServer extends BaseService
{
    /**
     * 获取人脸比对分数
     * @param $userId
     * @param bool $emptyWarning
     * @return float
     */
    public function getFaceMatchScore($userId, $emptyWarning = false)
    {
        $where = [
            'user_id' => $userId,
            'type' => UserThirdData::TYPE_FACE_COMPARISON,
        ];

        $model = UserThirdData::query()->where($where)
            ->orderBy('id', 'desc')
            ->first();

        $result = json_decode(optional($model)->result, true);

        if (!$result || !isset($result['score']) || !is_numeric($result['score'])) {
            // 不存在人脸记录
            if ($emptyWarning && !$model) {
                $merchantId = MerchantHelper::getMerchantId();
                DingHelper::notice("userId:{$userId},merchantId:{$merchantId}", '人脸比对分获取为空', DingHelper::AT_SOLIANG);
            }

            return (float)0;
        }

        return (float)bcmul($result['score'], 100, 2);
    }

    public function getPancardNameCheckRes($userId)
    {
        $result = $this->getDataByType($userId, UserThirdData::TYPE_PANCARD_NAME_CHECK);

        if (!$result) {
            return true;
        }

        return $result->res_status == UserThirdData::RES_STATUS_SUCCESS || $result->res_status == UserThirdData::RES_STATUS_FAIL;
    }

    protected function getDataByType($userId, $type)
    {
        return (new UserThirdData())->getByType($userId, $type);
    }

    public function getPancardOcrVerfiyNameCheck($userId)
    {
        $result = $this->getDataByType($userId, UserThirdData::TYPE_PANCARD_OCR_VERFIY_NAME_CHECK);

        if (!$result) {
            return true;
        }

        return $result->res_status == UserThirdData::RES_STATUS_SUCCESS || $result->res_status == UserThirdData::RES_STATUS_FAIL;
    }

    public function getAadhaarCardTelephoneCheck($userId)
    {
        $result = $this->getDataByType($userId, UserThirdData::TYPE_AADHAAR_CARD_TELEPHONE_CHECK);

        if (!$result) {
            return true;
        }

        return $result->res_status == UserThirdData::RES_STATUS_SUCCESS || $result->res_status == UserThirdData::RES_STATUS_FAIL;
    }

    public function getAadhaarCardAgeCheck($userId)
    {
        $result = $this->getDataByType($userId, UserThirdData::TYPE_AADHAAR_CARD_AGE_CHECK);

        if (!$result) {
            return true;
        }

        return $result->res_status == UserThirdData::RES_STATUS_SUCCESS || $result->res_status == UserThirdData::RES_STATUS_FAIL;
    }

    public function getBankBenenameFullnameCheck($userId, $bankname = '')
    {
        $result = $this->getDataByType($userId, UserThirdData::TYPE_BANKNAME_CHECK);

        if (!$result) {
            return true;
        }

        if (!$data = json_decode($result->result, true)) {
            return true;
        }
        if (!isset($data['fullname']) || !isset($data['bankname'])) {
            return true;
        }

        $bankname = trim($bankname);
        // 0、Unregistered 和 'CANARA BANK', 'KARUR VYSYA BANK', 'VIJAYA BANK'  1、SYNDICATE BANK返回NA;2、VIJAYA BANK返回MB;3、ANDHRA BANK返回IMPS CUSTOMER；4、BANK OF INDIA返回包含数字
        if (trim($data['bankname']) == 'Unregistered' && in_array($bankname, ['CANARA BANK', 'KARUR VYSYA BANK', 'VIJAYA BANK'])) {
            return true;
        }
        if (trim($data['bankname']) == 'NA' && $bankname == 'SYNDICATE BANK') {
            return true;
        }
        if (trim($data['bankname']) == 'MB' && $bankname == 'VIJAYA BANK') {
            return true;
        }
        if (trim($data['bankname']) == 'IMPS CUSTOMER' && $bankname == 'ANDHRA BANK') {
            return true;
        }
        if (preg_match('/\\d+$/', trim($data['bankname'])) && $bankname == 'BANK OF INDIA') {
            return true;
        }

        return StringHelper::stringIntersectByBank2($data['fullname'], $data['bankname']);
    }

    public function getPanName($userId)
    {
        $result = (new UserThirdData())->getDataByType($userId, UserThirdData::TYPE_PAN_CARD_FRONT);
        if (!$result || !is_array($result)) {
            return '';
        }
        return array_get($result, 'name', '');
    }

    protected function getDataResultByType($userId, $type)
    {
        return (new UserThirdData())->getDataByType($userId, $type);
    }
}
