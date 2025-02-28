<?php

namespace Api\Models\Notice;

use Common\Utils\MerchantHelper;

/**
 * Api\Models\Notice\Notice
 *
 * @property int $id 通知id
 * @property string $platform 平台：all、ios、android
 * @property string $title 标题
 * @property string|null $tags 标签
 * @property string $content 内容
 * @property int $read_total 阅读量
 * @property int $status 状态（审核中0、待推送1、已推送2）
 * @property string $created_at 创建时间
 * @property string $updated_at 创建时间
 * @property string $pushed_at 推送时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Notice\Notice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Notice\Notice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Notice\Notice query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Notice\Notice whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Notice\Notice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Notice\Notice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Notice\Notice wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Notice\Notice wherePushedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Notice\Notice whereReadTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Notice\Notice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Notice\Notice whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Notice\Notice whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Notice\Notice whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Notice\Notice orderByCustom($column = null, $direction = 'asc')
 * @property int $app_id app_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Notice\Notice whereAppId($value)
 */
class Notice extends \Common\Models\Notice\Notice
{

    const SCENARIO_URGENT = 'urgent';
    const SCENARIO_TIP = 'tip';

    public function texts()
    {
        return [
            self::SCENARIO_URGENT => [
                'id',
                'title',
                'content',
                'pushed_at',
            ],
            self::SCENARIO_TIP => [
                'id',
                'title',
                'content',
                'pushed_at',
            ],
        ];
    }

    public function getList($userId, $page = 1, $size = 10)
    {
        $offset = ($page - 1) * $size;
        $inbox = Notice::model()->newQueryWithoutScopes()
            ->select($this->dbRaw("id, title, content, pushed_at as datetime, 'notice' as type, 0 as is_read,tags,link_address"))
            ->where('status', Notice::STATUS_SENDED)
            ->where('app_id', MerchantHelper::getAppId())->orderByDesc('tags')
            ->orderByDesc('datetime')->offset($offset)->limit($size)->get();
        return $inbox;
    }

    public function getOne($param)
    {
        $query = $this->newQueryWithoutScopes();
        $query->select($this->dbRaw('id, title, content, pushed_at as datetime, "notice" as type, 1 as is_read,tags,link_address'));
        $query->where('id', $param['id']);
        return $query->first();
    }

    public function getOneByType($tags)
    {
        $where = [
            'tags' => $tags,
            'status' => self::STATUS_SENDED,
        ];
        return $this->where($where)->orderByDesc('pushed_at')->first();
    }

}
