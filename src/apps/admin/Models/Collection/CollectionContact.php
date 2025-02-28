<?php

namespace Admin\Models\Collection;

use Common\Utils\MerchantHelper;

/**
 * Admin\Models\Collection\CollectionContact
 *
 * @property int $id
 * @property int $order_id 订单ID
 * @property int $user_id 用户ID
 * @property int $collection_id 订单ID
 * @property string $type 联系来源类型
 * @property string $fullname 姓名
 * @property string $contact 联系值（手机号）
 * @property string $relation 关系
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionContact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionContact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionContact query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionContact whereCollectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionContact whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionContact whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionContact whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionContact whereRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionContact whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionContact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionContact whereUserId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionContact orderByCustom($column = null, $direction = 'asc')
 * @property string $content
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionContact whereContent($value)
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionContact whereMerchantId($value)
 */
class CollectionContact extends \Common\Models\Collection\CollectionContact
{

    const SCENARIO_LIST = 'list';
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'order_id',
                'user_id',
                'collection_id',
                'type',
                'fullname',
                'contact',
                'relation',
                'content',
            ],
            self::SCENARIO_UPDATE => [
                'fullname',
                'relation',
            ],
        ];
    }

    public function textRules()
    {
        return [
            'array' => [
                'relation' => ts(CollectionContact::RELATION, 'collection'),
            ],
            'function' => [
                'id' => function () {
                    if ($this->scenario == self::SCENARIO_LIST) {
                        $this->collectionRecord && $this->collectionRecord->getText();
                        /*if ($this->type == CollectionContact::TYPE_TELEPHONE_CONTACT) {
                            $content = json_decode($this->content, true);
                            $callCountOut = array_get($content, 'call_count_out');
                            $callCountIn = array_get($content, 'call_count_in');
                            if (isset($callCountOut) && isset($callCountIn)) {
                                $this->contact = $this->contact . " 呼出：$callCountOut, 呼入：$callCountIn";
                            }
                        }*/
                    }
                },
            ],
        ];
    }

    public function collectionRecord($class = CollectionRecord::class)
    {
        return parent::collectionRecord($class);
    }

    public function create($data)
    {
        return self::model(self::SCENARIO_CREATE)->saveModel($data);
    }

    public function getOne($id)
    {
        return self::where('id', $id)->first();
    }

    public function updateContact($contact, $data)
    {
        return $contact->setScenario(self::SCENARIO_UPDATE)->saveModel($data);
    }

    /**
     * 根据订单ID获取当前联系人数
     *
     * @param $orderId
     * @return mixed
     */
    public function getCollectionContactsCount($orderId, $type = [])
    {
        $query = self::where('order_id', $orderId);
        if($type){
            $query->whereIn('type', (array)$type);
        }
        return $query->count();
    }

    /**
     * 根据数量，类型获取联系人列表
     *
     * @param int $orderId
     * @param int $num
     * @return mixed
     */
    public function getCollectionContacts($collectionId, $num = 0, $type = '')
    {
        $query = CollectionContact::where('collection_id', $collectionId);
        if ($type != '') {
            $query->whereIn('type', (array)$type);
        }
        if ($num > 0) {
            $query->limit($num);
        }
        $query->orderByDesc("type");
        return $query->get();
    }

    public function getUserSelfContact($orderId)
    {
        $where = [
            'type' => self::TYPE_USER_SELF,
            'order_id' => $orderId,
        ];
        return self::query()->where($where)->first();
    }
}
