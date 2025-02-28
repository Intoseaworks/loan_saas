<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Collection;

use Admin\Models\Collection\Collection;
use Admin\Models\Collection\CollectionContact;
use Admin\Services\BaseService;
use Admin\Services\Risk\RiskServer;
use Common\Models\UserData\UserContactsTelephone;
use Common\Utils\Data\StringHelper;
use Common\Utils\LoginHelper;
use Illuminate\Support\Str;

class CollectionContactServer extends BaseService
{
    /**
     * @param $data
     * @return CollectionContact|bool|void
     * @throws \Common\Exceptions\ApiException
     */
    public function create($data)
    {
        $collection = Collection::model()->getOne($data['collection_id']);
        if (!$collection) {
            return $this->outputException('对应催收记录不存在');
        }
        CollectionServer::server()->checkCaseBelong($collection);
        $data['order_id'] = $collection->order_id;
        $data['user_id'] = $collection->user_id;
        $data['collection_id'] = $collection->id;
        $data['type'] = CollectionContact::TYPE_COLLECTION_CONTACT;
        return CollectionContact::model()->create($data);
    }

    /**
     * @param $collection
     * @param int $num
     * @return mixed
     */
    public function getMessageContact($collection, $num = 50)
    {
        if ($num > CollectionContact::model()->getCollectionContactsCount($collection->order_id, CollectionContact::TYPE_MESSAGE_CONTACT)) {
            $concatTelephoneCallDatas = (new UserContactsTelephone())->getContacts($collection->user_id, $num);
            CollectionContactServer::server()->saveTelephoneCall($collection, $concatTelephoneCallDatas->toArray());
        }
        return CollectionContact::model()->getCollectionContacts($collection->id, $num, CollectionContact::TYPE_MESSAGE_CONTACT);
    }

    /**
     * @param $collection
     * @param $datas
     */
    public function saveTelephoneCall($collection, $datas)
    {
        if (!is_array($datas)) {
            return false;
        }
        $oldTelephones = CollectionContactServer::server()->cleckTelephones($collection->order_id, array_column($datas, 'receive_phone'))->toArray();
        $oldTelephonesCount = count($oldTelephones);
        foreach ($datas as $data) {
            if ($oldTelephonesCount && in_array($data['receive_phone'], $oldTelephones)) {
                continue;
            }
            //电话号码格式过滤
            $data['contact_telephone'] = StringHelper::formatTelephone($data['contact_telephone']);
            //菲律宾手机
//            if (!preg_match('/[98]\d{9}$/', $data['contact_telephone'])){
            if (!preg_match('/\d{10}$/', $data['contact_telephone'])){
                continue;
            }
            $userContactData = [
                'order_id' => $collection->order_id,
                'user_id' => $collection->user_id,
                'collection_id' => $collection->id,
                'type' => CollectionContact::TYPE_MESSAGE_CONTACT,
                'fullname' => $data['contact_fullname'],
                'contact' => $data['contact_telephone'],
                'content' => json_encode($data, 256),
            ];
            //有记录则不加
            if (CollectionContact::model()->where([
                'order_id' => $collection->order_id,
                'user_id' => $collection->user_id,
                'collection_id' => $collection->id,
                'contact' => $data['contact_telephone']
            ])->first()){
                continue;
            }
            CollectionContact::model()->create($userContactData);
        }
    }

    /**
     * @param $orderId
     * @param $telephones
     * @return mixed
     */
    public function cleckTelephones($orderId, $telephones)
    {
        return CollectionContact::model()
            ->where('order_id', $orderId)
            ->whereIn('contact', $telephones)
            ->pluck('contact');
    }

}
