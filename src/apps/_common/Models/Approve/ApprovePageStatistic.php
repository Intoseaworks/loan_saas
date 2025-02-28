<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-01-17
 * Time: 10:00
 */

namespace Common\Models\Approve;


use Approve\Admin\Services\Approval\WorkFlowService;
use Common\Traits\Model\GlobalScopeModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Approve\ApprovePageStatistic
 *
 * @property int $id
 * @property int $approve_user_pool_id approve_user_pool 表主键
 * @property int $page 审批页
 * @property int|null $cost 花费 单位秒
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Common\Models\Approve\ApproveUserPool $approveUserPool
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePageStatistic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePageStatistic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePageStatistic query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePageStatistic whereApproveUserPoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePageStatistic whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePageStatistic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePageStatistic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePageStatistic wherePage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePageStatistic whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $type 审批类型 1初审 2电审
 * @property int $admin_id 审批人员
 * @property int $status 统计是否有效 1未生效 2有效 审批人员最终提交审批单统计才有效
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePageStatistic whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePageStatistic whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePageStatistic whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePageStatistic effective()
 * @property int $merchant_id
 * @property int $order_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePageStatistic whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePageStatistic whereOrderId($value)
 */
class ApprovePageStatistic extends Model
{
    use GlobalScopeModel;

    /**
     * 初审基础资料页
     */
    const CASHNOW_PAGE_BASE_DETAIL = 1;

    /**
     * 银行流水
     */
    const CASHNOW_PAGE_BANK_STATEMENT = 2;

    /**
     * 工资单
     */
    const CASHNOW_PAGE_PAYSLIP = 3;

    /**
     * 员工证
     */
    const CASHNOW_PAGE_EMPLOYEE_CARD = 4;

    /**
     * 就业信息
     */
    const CASHNOW_PAGE_EMPLOYEE_INFO = 5;

    /**
     * 电审资料详情
     */
    const CASHNOW_PAGE_CALL_BASEDETAIL = 6;

    /**
     * 状态,统计未生效
     */
    const STATUS_INEFFECTIVE = 1;

    /**
     * 状态,统计有效
     */
    const STATUS_EFFECTIVE = 2;

    /**
     * 审批类型,初审
     */
    const TYPE_FIRST_APPROVE = 1;

    /**
     * 审批类型,电审
     */
    const TYPE_CALL_APPROVE = 2;

    /**
     * @var string
     */
    protected $table = 'approve_page_statistic';

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
     * @param $data
     * @return bool
     */
    public static function saveData($data)
    {
        return static::updateOrCreate(
            [
                'approve_user_pool_id' => $data['approve_user_pool_id'],
                'page' => $data['page']
            ], $data);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approveUserPool()
    {
        return $this->belongsTo(ApproveUserPool::class, 'approve_user_pool_id', 'id');
    }

    /**
     * @param $submit
     * @return int|mixed
     */
    public function getCashnowStatisticPage($submit)
    {
        $list = [
            WorkFlowService::BASE_DETAIL => static::CASHNOW_PAGE_BASE_DETAIL,
            WorkFlowService::BANK_STATEMENT => static::CASHNOW_PAGE_BANK_STATEMENT,
            WorkFlowService::PAY_SLIP => static::CASHNOW_PAGE_PAYSLIP,
            WorkFlowService::EMPLOYEE_CARD => static::CASHNOW_PAGE_EMPLOYEE_CARD,
            WorkFlowService::EMPLOYMENT_INFO => static::CASHNOW_PAGE_EMPLOYEE_INFO,
        ];

        return $list[$submit] ?? 0;
    }

    /**
     * Scope a query to only include active users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEffective($query)
    {
        return $query->where('approve_page_statistic.status', static::STATUS_EFFECTIVE);
    }

}
