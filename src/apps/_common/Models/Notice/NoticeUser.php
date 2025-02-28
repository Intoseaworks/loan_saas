<?php

/**
 *
 *
 */
namespace Common\Models\Notice;

use Common\Models\Inbox\Inbox;
use Common\Models\User\User;
use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class NoticeUser extends Model {

    use StaticModel;

    const TYPE_NOTICE = 1;
    const TYPE_SMS = 2;

    protected $fillable = ['id','user_id','task_id','type','created_at','telephone','fullname','notice_id'];

    protected $table = 'user_notice';

    public function getNoticeList($userId, $page = 1, $size = 10)
    {
        $offset = ($page - 1) * $size;
        $notice = NoticeUser::model()
            ->join("notice_template","user_notice.notice_id","=","notice_template.id")
            ->select($this->dbRaw("user_notice.id, notice_template.title, notice_template.content, user_notice.created_at as datetime, 'inbox_notice' as type, 0 as is_read"))
            ->where('user_id', $userId)
            ->where('type', 1);
        $inbox = Inbox::model()
            ->select($this->dbRaw("id, title, content, created_at as datetime, 'inbox' as type, status as is_read"))->where('user_id',
                $userId)
            ->union($notice)
            ->orderByDesc('datetime')->offset($offset)->limit($size)->get();
        return $inbox;
//        $query = self::query()->select('*');
//
//        // 发送时间
//        $time_start = array_get($params, 'time_start');
//        $time_end = array_get($params, 'time_end');
//
//        if ($time_start && $time_end) {
//            $query->whereBetween('user_notice.created_at', [$time_start, $time_end]);
//        }
//
//        if ($task_id = array_get($params, 'task_id')) {
//            $query->where('user_notice.task_id', $task_id);
//        }
//        // 用户名
//        $keyword = array_get($params, 'keyword_user');
//        if (isset($keyword)) {
//            $keyword = trim($keyword);
//            $query->where(function ($query) use ($keyword) {
//                $query->where('fullname', 'like', $keyword);
//                $query->orWhere('telephone', 'like', $keyword);
//            });
//        }
//        $keyword = array_get($params, 'keyword_sms_task');
////        if (isset($keyword)) {
////            $keyword = trim($keyword);
////            $query->where('sms_centent', 'like', $keyword);
////        }
//        $size = array_get($params, 'size');
//        return $query->orderBy('id', 'desc')->paginate($size);
    }

}
