<?php

namespace Api\Services\Inbox;

use Api\Models\Inbox\Inbox;
use Api\Models\Notice\Notice;
use Api\Services\BaseService;
use Common\Exceptions\ApiException;
use Common\Models\Notice\NoticeUser;
use Common\Redis\Notice\NoticeRedis;
use Common\Utils\MerchantHelper;
use Common\Utils\Push\Jpush;

class InboxServer extends BaseService
{

    /**
     * @param $userId
     * @param int $page
     * @param int $size
     * @return array
     */
    public function getList($userId, $page = 1, $size = 10, $type = '')
    {
        $unread = 0;
        if ($type == 'inbox') {
            $items = Inbox::model()->getList($userId, $page, $size);
//            $items = NoticeUser::model()->getNoticeList($userId, $page, $size);
            $unread = Inbox::model()->query()->where("user_id", $userId)
                    ->where("status", "0")
                    ->count();
        } else if ($type == 'notice') {
            $items = Notice::model()->getList($userId, $page, $size);
            $read = \DB::table('notice_user')->where('user_id',$userId)->pluck('notice_id');
//            $unread = Notice::model()->newQueryWithoutScopes()->where('status', Notice::STATUS_SENDED)->whereNotIn('id',$read)->count();
            $unread = Notice::model()->newQueryWithoutScopes()->where('app_id', MerchantHelper::getAppId())->where('status', Notice::STATUS_SENDED)->whereNotIn('id',$read)->count();
        } else {
            $items = Inbox::model()->getAllList($userId, $page, $size);
        }

        foreach ($items as $item) {
            if ($item->type == Jpush::TYPE_NOTICE) {
                $item->is_read = NoticeRedis::redis()->isread($item->id, $userId);
                if ( $item->is_read == 0 && \DB::table('notice_user')->where('user_id',$userId)->where('notice_id',$item->id)->first()){
                    $item->is_read = 1;
                    NoticeRedis::redis()->read($item->id, $userId);
                }
            }
        }
        $result = [
            'unread' => $unread ?: 0,
            'items' => $items,
            'page' => $page,
            'size' => $size,
            'total' => count($items),
        ];
        return $result;
    }

    /**
     * @param $param
     * @return Inbox|\Illuminate\Database\Eloquent\Model|object|null
     * @throws \Common\Exceptions\ApiException
     */
    public function getOne($param)
    {
        $inbox = Inbox::model()->getOne($param);
        if (!$inbox) {
            throw new ApiException('记录不存在');
        }
        if ($inbox->status == Inbox::INBOX_STATUS_UNREAD) {
            Inbox::model()->updateStauts($inbox, Inbox::INBOX_STATUS_READ);
        }
        return $inbox;
    }

    public function setRead($param)
    {
        foreach ($param['id'] as $id){
            $newParam['id'] = $id;
            $newParam['user_id'] = $param['user_id'];
            $inbox = Inbox::model()->getOne($newParam);
            if (!$inbox) {
                throw new ApiException('记录不存在');
            }
            if ($inbox->status == Inbox::INBOX_STATUS_UNREAD) {
                Inbox::model()->updateStauts($inbox, Inbox::INBOX_STATUS_READ);
            }
        }
        return true;
    }

    public function hasUnreadMessage($userId)
    {
        $items = Inbox::model()->getAllList($userId, $page = 1, $size = 999999);
        foreach ($items as $item) {
            if ($item->type == Jpush::TYPE_NOTICE) {
                if (!NoticeRedis::redis()->isread($item->id, $userId)) {
                    return true;
                }
            } else {
                if ($item->is_read == Inbox::INBOX_STATUS_UNREAD) {
                    return true;
                }
            }
        }
        return false;
    }
}
