<?php

namespace Common\Models\Notice;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Notice\Notice
 *
 * @property int $id 通知id
 * @property string $platform 平台：all、ios、android
 * @property string $title 标题
 * @property string|null $tags 标签
 * @property string $content 内容
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property int $pushed_at 推送时间
 * @property int $read_total 阅读量
 * @property int $status 状态（审核中0、待推送1、已推送2）
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Notice\Notice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Notice\Notice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Notice\Notice query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Notice\Notice whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Notice\Notice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Notice\Notice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Notice\Notice wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Notice\Notice wherePushedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Notice\Notice whereReadTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Notice\Notice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Notice\Notice whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Notice\Notice whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Notice\Notice whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Notice\Notice orderByCustom($column = null, $direction = 'asc')
 * @property int $app_id app_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Notice\Notice whereAppId($value)
 */
class Notice extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'notice';

    /**
     * 紧急状态
     */
    const TAGS_URGENT = 'urgent';
    const TAGS_NORMAL = 'normal';
    const TAGS = [
        self::TAGS_URGENT => '紧急公告',
        self::TAGS_NORMAL => '一般公告',
    ];

    const STATUS_WAITING_SEND = 1;
    const STATUS_SENDED = 2;
    const STATUS_DELETED = 3;
    const STATUS = [
        self::STATUS_WAITING_SEND => '待发送',
        self::STATUS_SENDED => '已发送',
        self::STATUS_DELETED => '已删除',
    ];

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];

    protected static function boot()
    {
        parent::boot();

        static::setAppIdBootScope();
    }


    public function textRules()
    {
        return [
            'array' => [
                'tags' => ts(self::TAGS, 'notice'),
                'status' => ts(self::STATUS, 'notice'),
            ],
        ];
    }

}
