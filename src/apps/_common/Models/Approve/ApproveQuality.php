<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-01-11
 * Time: 16:42
 */

namespace Common\Models\Approve;


use Common\Traits\Model\GlobalScopeModel;
use Common\Utils\LoginHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Approve\ApproveQuality
 *
 * @property int $id
 * @property int $approve_user_pool_id approve_user_pool 主键
 * @property int $quality_status 质检状态 1 未质检 2已质检 默认 1
 * @property int|null $quality_result 质检结果 1通过误判为拒绝 2拒绝误判为通过 3正确审核
 * @property int|null $admin_id 质检人 id
 * @property int|null $quality_time 质检时间
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Common\Models\Approve\ApproveUserPool $approveUserPool
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveQuality newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveQuality newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveQuality query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveQuality whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveQuality whereApproveUserPoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveQuality whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveQuality whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveQuality whereQualityResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveQuality whereQualityStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveQuality whereQualityTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveQuality whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $remark
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveQuality whereRemark($value)
 * @property int $merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveQuality whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveQuality whereOrderId($value)
 * @property string|null $change_order 修改订单状态
 * @method static \Illuminate\Database\Eloquent\Builder|ApproveQuality whereChangeOrder($value)
 */
class ApproveQuality extends Model
{
    use GlobalScopeModel;

    /**
     *  质检状态 未质检
     */
    const QUALITY_STATUS_WAITING = 1;

    /**
     * 质检状态 已质检
     */
    const QUALITY_STATUS_DONE = 2;

    /**
     * 质检结果 通过误判为拒绝
     */
    const QUALITY_RESULT_1 = 1;

    /**
     * 质检结果 拒绝误判为通过
     */
    const QUALITY_RESULT_2 = 2;

    /**
     * 质检结果 正确审核
     */
    const QUALITY_RESULT_3 = 3;

    /**
     * 质检结果 正确拒绝但下错拒绝码
     */
    const QUALITY_RESULT_4 = 4;

    /**
     * 订单状态流转
     */
    const CHANGE_ORDER_TO_PASS = 'to_pass';

    /**
     * @var string
     */
    protected $table = 'approve_quality';

    /**
     * @var array
     */
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approveUserPool()
    {
        return $this->belongsTo(ApproveUserPool::class, 'approve_user_pool_id', 'id');
    }

    /**
     * @param null|int $status
     * @return string
     */
    public function getQualityResultText($status = null)
    {
        if (is_null($status)) {
            $status = $this->quality_result;
        }
        return $this->getQualityResultList()[(int)$status] ?? '';
    }

    /**
     * @return array
     */
    public function getQualityResultList()
    {
        return [
            static::QUALITY_RESULT_1 => t('通过误判为拒绝', 'approve'),
            static::QUALITY_RESULT_2 => t('拒绝误判为通过', 'approve'),
            static::QUALITY_RESULT_4 => t('正确拒绝但下错拒绝码', 'approve'),
            static::QUALITY_RESULT_3 => t('正确审核', 'approve'),
        ];
    }

    /**
     * @param null|string $status
     * @return string
     */
    public function getQualityStatusText($status = null)
    {
        if (is_null($status)) {
            $status = $this->quality_status;
        }
        return $this->getQualityStatusList()[$status] ?? '';
    }

    /**
     * @return array
     */
    public function getQualityStatusList()
    {
        return [
            static::QUALITY_STATUS_WAITING => t('未质检', 'approve'),
            static::QUALITY_STATUS_DONE => t('已质检', 'approve'),
        ];
    }

    public function setQualityResult($data)
    {
        $this->quality_result = array_get($data, 'quality_result', '');
        $this->remark = array_get($data, 'remark', '');
        if($passOrder = array_get($data, 'pass_order')){
            $this->change_order = self::CHANGE_ORDER_TO_PASS;
        }
    }

     public function saveQualityResult()
     {
         $this->quality_status = ApproveQuality::QUALITY_STATUS_DONE;
         $this->admin_id = LoginHelper::getAdminId();
         $this->quality_time = time();
         return $this->save();
     }

}
