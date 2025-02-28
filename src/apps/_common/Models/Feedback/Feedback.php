<?php

namespace Common\Models\Feedback;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Feedback\Feedback
 *
 * @property int $id 反馈ID
 * @property int|null $user_id 用户ID
 * @property string|null $type 类型
 * @property string $content 内容
 * @property string|null $telephone 联系电话
 * @property int|null $status 状态 1正常 0废弃
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\Feedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\Feedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\Feedback query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\Feedback whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\Feedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\Feedback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\Feedback whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\Feedback whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\Feedback whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\Feedback whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\Feedback whereUserId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\Feedback orderByCustom($column = null, $direction = 'asc')
 * @property int $app_id feedback
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\Feedback whereAppId($value)
 */
class Feedback extends Model
{
    use StaticModel;

    const STATUS_DISCARD = 0;
    const STATUS_NOMAL = 1;

    /** 反馈类型 */
    const TYPE_PRODUCT_PROPOSAL = 'product_suggestion';//产品建议
    const TYPE_SERVICE_COMPLAINT = 'service_complaints';//服务投诉
    const TYPE_PROGRAM_ERROR = 'program_error';//程序错误
    const TYPE = [
        self::TYPE_PRODUCT_PROPOSAL => 'Product Suggestion',
        self::TYPE_SERVICE_COMPLAINT => 'Service Complaints',
        self::TYPE_PROGRAM_ERROR => 'Program Error',
    ];
    /**
     * @var string
     */
    public $table = 'feedback';

    /**
     * @var bool
     */
    //public $timestamps = false;

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $dates = [];

    protected static function boot()
    {
        parent::boot();

        static::setAppIdBootScope();
    }

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [

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

    public function getType()
    {
        return self::TYPE;
    }

}
