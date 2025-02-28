<?php

namespace Common\Models\Message;

use Common\Models\Merchant\Merchant;
use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Message\Message
 *
 * @property int $id
 * @property int $merchant_id
 * @property int $type 信息类型(根据前端展示样式区分) 0:普通 1:succes 2:info 3:warning 4:error
 * @property int $send_type 发送类型 1:系统
 * @property string|null $title
 * @property string|null $content
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Message newModelQuery()
 * @method static Builder|Message newQuery()
 * @method static Builder|Message orderByCustom($defaultSort = null)
 * @method static Builder|Message query()
 * @method static Builder|Message whereContent($value)
 * @method static Builder|Message whereCreatedAt($value)
 * @method static Builder|Message whereId($value)
 * @method static Builder|Message whereMerchantId($value)
 * @method static Builder|Message whereSendType($value)
 * @method static Builder|Message whereStatus($value)
 * @method static Builder|Message whereTitle($value)
 * @method static Builder|Message whereType($value)
 * @method static Builder|Message whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Message extends Model
{
    use StaticModel;

    protected $table = 'message';

    protected $guarded = [];

    /** 状态：已删除 */
    const STATUS_DELETE = -1;
    /** 状态：创建 */
    const STATUS_CREATE = 1;
    /** 状态：已发送 */
    const STATUS_SEND = 2;
    /** 状态 */
    const STATUS = [
        self::STATUS_DELETE => '已删除',
        self::STATUS_CREATE => '创建',
        self::STATUS_SEND => '已发送',
    ];

    /** 类型：普通 */
    const TYPE_NONE = 0;
    /** 类型：成功 */
    const TYPE_SUCCESS = 1;
    /** 类型：info */
    const TYPE_INFO = 2;
    /** 类型：warning */
    const TYPE_WARNING = 3;
    /** 类型：error */
    const TYPE_ERROR = 4;
    /** 类型 */
    const TYPE = [
        self::TYPE_NONE => 'NONE',
        self::TYPE_SUCCESS => 'SUCCESS',
        self::TYPE_INFO => 'INFO',
        self::TYPE_WARNING => 'WARNING',
        self::TYPE_ERROR => 'ERROR',
    ];

    const SEND_TYPE_SYSTEM = 1;
    const SEND_TYPE = [
        self::SEND_TYPE_SYSTEM => '系统',
    ];

    /** 每日放款剩余预警 */
    const TEMPLATE_DAILY_REMAIN_AMOUNT_WARNING = 'daily_remain_amount_warning';
    /** 每日订单创建剩余量预警 */
    const TEMPLATE_DAILY_REMAIN_CREATE_WARNING = 'daily_remain_create_warning';
    /** 消息模板 */
    const TEMPLATE = [
        self::TEMPLATE_DAILY_REMAIN_AMOUNT_WARNING => [
            'type' => self::TYPE_WARNING,
            'send_type' => self::SEND_TYPE_SYSTEM,
            'title' => 'Loan Warning',
            'content' => 'The remaining loans amount available today are {amount}!',
        ],
        self::TEMPLATE_DAILY_REMAIN_CREATE_WARNING => [
            'type' => self::TYPE_WARNING,
            'send_type' => self::SEND_TYPE_SYSTEM,
            'title' => 'Loan Warning',
            'content' => 'The remaining loans that {userTypeText} available today are {count}!',
        ],
    ];

    public static function boot()
    {
        parent::boot();

        self::setMerchantIdBootScope();

        static::addGlobalScope('valid', function (Builder $builder) {
            $builder->where('status', '!=', self::STATUS_DELETE);
        });
    }

    public static function add($merchantId, string $template, array $templateParams = [], array $params = [])
    {
        $template = array_get(self::TEMPLATE, $template);

        if (!$template) {
            throw new \Exception('未找到消息模板');
        }

        $replace = [];
        foreach ($templateParams as $k => $v) {
            $replace["{{$k}}"] = $v;
        }
        $content = str_replace(array_keys($replace), array_values($replace), $template['content'] ?? '');

        $data = array_merge($template, $params, [
            'merchant_id' => $merchantId,
            'content' => $content,
            'status' => self::STATUS_CREATE,
        ]);

        return self::create($data);
    }

    public function statusToSend()
    {
        $this->status = self::STATUS_SEND;
        return $this->save();
    }

    public function messageUser($class = MessageUser::class)
    {
        return $this->hasMany($class, 'message_id', 'id');
    }

    /**
     * 关联 merchant 表
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function merchant($class = Merchant::class)
    {
        return $this->belongsTo($class, 'merchant_id', 'id');
    }
}
