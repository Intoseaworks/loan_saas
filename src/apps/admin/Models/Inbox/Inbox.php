<?php
/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/1/30
 * Time: 10:07
 */

namespace Admin\Models\Inbox;


use Common\Models\Merchant\App;

/**
 * Admin\Models\Inbox\Inbox
 *
 * @property int $id 私信id
 * @property int $user_id 用户id
 * @property string $title 标题
 * @property string $content 内容
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property string $read_time 读取时间
 * @property int $status 状态（未读、已读）
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Inbox\Inbox newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Inbox\Inbox newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Inbox\Inbox query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Inbox\Inbox whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Inbox\Inbox whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Inbox\Inbox whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Inbox\Inbox whereReadTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Inbox\Inbox whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Inbox\Inbox whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Inbox\Inbox whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Inbox\Inbox whereUserId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Inbox\Inbox orderByCustom($column = null, $direction = 'asc')
 * @property int $app_id app_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Inbox\Inbox whereAppId($value)
 */
class Inbox extends \Common\Models\Inbox\Inbox
{
    const SCENARIO_LIST = 'list';

    public function safes()
    {
        return [];
    }

    public function texts()
    {
        return [
            self::SCENARIO_LIST => [
                'id',
                'app_id',
                'title',
                'content',
                'created_at',
            ],
        ];
    }

    public function textRules()
    {
        return [
            'datetime' => [
                'created_at',
                'updated_at',
                'read_time',
            ],
            'array' => [
                'app_id' => App::getIdNameArr(),
            ],
        ];
    }

    public function getInboxList($params)
    {
        $query = self::query()->select('inbox.*')->with(['user']);

        //排序
        $query->orderByCustom();

        // 推送时间
        $time_start = array_get($params, 'time_start');
        $time_end = array_get($params, 'time_end');
        if ($time_start && $time_end) {
            $query->whereBetween('inbox.created_at', [$time_start, $time_end]);
        }

        // app筛选
        if($appId = array_get($params, 'app_id')){
            $query->where('app_id', $appId);
        }

        // 用户关键词
        $keyword = array_get($params, 'keyword_user');
        if (isset($keyword)) {
            $query->whereHas('user', function ($user) use ($keyword) {
                $keyword = '%' . $keyword . '%';
                $user->where('fullname', 'like', $keyword);
                $user->orWhere('telephone', 'like', $keyword);
            });
        }

        // 推送关键词
        $keywordInbox = array_get($params, 'keyword_inbox');
        if (isset($keywordInbox)) {
            $query->where(function ($query) use ($keywordInbox) {
                $keywordInbox = '%' . trim($keywordInbox) . '%';
                $query->where('title', 'like', $keywordInbox);
                $query->orWhere('content', 'like', $keywordInbox);
            });
        }
        $size = array_get($params, 'size');
        return $query->orderBy('inbox.id', 'desc')->paginate($size);
    }
}
