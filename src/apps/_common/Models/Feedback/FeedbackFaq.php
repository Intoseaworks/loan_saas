<?php

namespace Common\Models\Feedback;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Feedback\FeedbackFaq
 *
 * @property int $id 反馈ID
 * @property string|null $type 类型
 * @property string $title 标题
 * @property string $content 内容
 * @property int|null $status 状态 1正常 0废弃
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\FeedbackFaq newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\FeedbackFaq newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\FeedbackFaq query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\FeedbackFaq whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\FeedbackFaq whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\FeedbackFaq whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\FeedbackFaq whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\FeedbackFaq whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\FeedbackFaq whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\FeedbackFaq whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\FeedbackFaq orderByCustom($column = null, $direction = 'asc')
 */
class FeedbackFaq extends Model
{
    use StaticModel;

    /** @var int 正常 */
    const STATUS_NORMAL = 1;
    /** @var int 禁用 */
    const STATUS_DISABLE = 0;
    /** @var array status */
    const STATUS = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_DISABLE => '禁用',
    ];

    const TYPE_LOAN = 'loan';
    const TYPE_REPAY = 'repay';
    const TYPE_QUOTA = 'quota';
    const TYPE = [
        self::TYPE_LOAN => '借款',
        self::TYPE_REPAY => '还款',
        self::TYPE_QUOTA => '额度',
    ];


    const SCENE_CREATE = 'create';
    /**
     * @var string
     */
    protected $table = 'feedback_faq';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];

    public function safes()
    {
        return [
            self::SCENE_CREATE => [
                'type',
                'title',
                'content',
                'status' => self::STATUS_NORMAL,
                'created_at' => $this->getDate(),
                'updated_at' => $this->getDate(),
            ]
        ];
    }

    public function textRules()
    {
        return [];
    }

    public function clear()
    {
        return self::query()->whereStatus(self::STATUS_NORMAL)->update(['status' => self::STATUS_DISABLE]);
    }

}
