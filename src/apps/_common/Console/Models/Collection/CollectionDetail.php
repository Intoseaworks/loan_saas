<?php

namespace Common\Console\Models\Collection;

use Common\Utils\MerchantHelper;

/**
 * Admin\Models\Collection\CollectionDetail
 *
 * @property int $id
 * @property int $order_id 订单id
 * @property int $collection_id 催收id
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @property string|null $promise_paid_time 承诺还款时间
 * @property string|null $record_time 催记时间
 * @property string $dial 联系结果 （正常联系，无法联系...）
 * @property string $progress 催收进度 （承诺还款，无意向...）
 * @property string $remark 备注
 * @mixin \Eloquent
 * @property int $contact_num 显示联系人数
 * @property string $reduction_setting 减免设置
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail orderByCustom($column = null, $direction = 'asc')
 */
class CollectionDetail extends \Common\Models\Collection\CollectionDetail
{

    const SCENARIO_CREATE_COLLECTION = 'create_collection';
    const SCENARIO_ASSIGN_AGAIN = 'assign_again';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE_COLLECTION => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'order_id',
                'collection_id',
                'contact_num',
                'reduction_setting',
            ],
            self::SCENARIO_ASSIGN_AGAIN => [
                'contact_num',
                'reduction_setting',
            ],
        ];
    }

    public function texts()
    {
        return [
        ];
    }

    public function textRules()
    {
        return [
        ];
    }


    public function createCollection($data)
    {
        return self::model(self::SCENARIO_CREATE_COLLECTION)->saveModel($data);
    }

    public function assignAgain(CollectionDetail $collectionDetail, $data)
    {
        return $collectionDetail->setScenario(self::SCENARIO_ASSIGN_AGAIN)->saveModel($data);
    }
}
