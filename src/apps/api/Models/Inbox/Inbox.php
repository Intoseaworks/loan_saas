<?php

namespace Api\Models\Inbox;

use Common\Models\Notice\Notice;
use Common\Utils\Data\DateHelper;

/**
 * Api\Models\Inbox\Inbox
 *
 * @property int $id 私信id
 * @property int $user_id 用户id
 * @property string $title 标题
 * @property string $content 内容
 * @property string $created_at 创建时间
 * @property string $read_time 读取时间
 * @property int $status 状态（未读、已读）
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Inbox\Inbox newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Inbox\Inbox newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Inbox\Inbox query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Inbox\Inbox whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Inbox\Inbox whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Inbox\Inbox whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Inbox\Inbox whereReadTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Inbox\Inbox whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Inbox\Inbox whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Inbox\Inbox whereUserId($value)
 * @mixin \Eloquent
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Inbox\Inbox whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Inbox\Inbox orderByCustom($column = null, $direction = 'asc')
 * @property int $app_id app_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Inbox\Inbox whereAppId($value)
 */
class Inbox extends \Common\Models\Inbox\Inbox
{

    const SCENARIO_READ = 'read';

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [
            self::SCENARIO_READ => [
                'status',
                'read_time' => DateHelper::dateTime(),
            ],
        ];
    }

    /**
     * 显示场景过滤
     * @return array
     */
    public function texts()
    {
        return [
        ];
    }

    /**
     * 显示非过滤
     * @return array
     */
    public function unTexts()
    {
        return [
        ];
    }

    /**
     * 显示规则
     * @return array
     */
    public function textRules()
    {
        return [
        ];
    }

    public function getList($userId, $page = 1, $size = 10)
    {
        $offset = ($page - 1) * $size;
        $inbox = Inbox::model()
            ->select($this->dbRaw("id, title, content, created_at as datetime, 'inbox' as type, status as is_read"))
            ->where('user_id', $userId)
            ->orderByDesc('datetime')->offset($offset)->limit($size)->get();
        return $inbox;
    }

    public function getAllList($userId, $page = 1, $size = 10)
    {
        $notice = Notice::model()
            ->select($this->dbRaw("id, title, content, pushed_at as datetime, 'notice' as type, 0 as is_read"))->where('status',
                Notice::STATUS_SENDED);
        $offset = ($page - 1) * $size;
        $inbox = Inbox::model()
            ->select($this->dbRaw("id, title, content, created_at as datetime, 'inbox' as type, status as is_read"))->where('user_id',
                $userId)
            ->union($notice)
            ->orderByDesc('datetime')->offset($offset)->limit($size)->get();
        return $inbox;
    }

    /**
     * @param $param
     * @return $this|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getOne($param)
    {
        $query = $this->newQuery();
        $query->select($this->dbRaw('id, title, content, created_at as datetime'));
        $query->where('id', $param['id']);
        $query->where('user_id', $param['user_id']);
        return $query->first();
    }

    public function updateStauts(Inbox $inbox, $status)
    {
        return $inbox->setScenario(self::SCENARIO_READ)->saveModel(['status' => $status]);
    }

}
