<?php

namespace Risk\Common\Models\Business;

use Common\Utils\Data\ArrayHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\DB;
use Risk\Common\Models\BatchSave;
use Risk\Common\Models\RiskBaseModel;

abstract class BusinessBaseModel extends RiskBaseModel
{
    use BatchSave;

    protected $chunkSize = 1000;

    public function batchAddRiskData($userId, $data)
    {
        if (!$data) {
            return true;
        }

        $data = ArrayHelper::isAssocArray($data) ? [$data] : $data;
        foreach ($data as &$item) {
            if (isset($item['app_id'])) {
                $item['business_app_id'] = $item['app_id'];
            }
            $item['app_id'] = MerchantHelper::getMerchantId();
            $item['user_id'] = $userId;

            $item = $this->itemFormat($item);
        }

        return DB::connection($this->getConnectionName())->transaction(function () use ($data) {
            // 防止数据量过大，出现 Prepared statement contains too many placeholders
            $chunkData = array_chunk($data, $this->chunkSize);
            foreach ($chunkData as $item) {
                $this->batchInsert($item);
            }

            return true;
        });
    }

    protected function itemFormat($item)
    {
        return $item;
    }

    public function batchAddRiskCommonData($data)
    {
        if (!$data) {
            return true;
        }

        $data = ArrayHelper::isAssocArray($data) ? [$data] : $data;
        foreach ($data as &$item) {
            if (isset($item['app_id'])) {
                $item['business_app_id'] = $item['app_id'];
            }
            $item['app_id'] = MerchantHelper::getMerchantId();

            $item = $this->itemFormat($item);
        }

        return DB::connection($this->getConnectionName())->transaction(function () use ($data) {
            // 防止数据量过大，出现 Prepared statement contains too many placeholders
            $chunkData = array_chunk($data, $this->chunkSize);
            foreach ($chunkData as $item) {
                $this->batchInsert($item);
            }

            return true;
        });
    }
}
