<?php

namespace Risk\Common\Models\CreditReport;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\CreditReport\ThirdExperian
 *
 * @property int $id
 * @property string $batch_no 批次编号
 * @property string|null $type 类型
 * @property string|null $telephone 手机号
 * @property string|null $request_info 请求信息
 * @property string|null $report_body 报告主体
 * @property string|null $response_info 响应原文
 * @property string|null $status 响应状态
 * @property string|null $extra_info
 * @property string|null $remark
 * @property string|null $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdExperian newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdExperian newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdExperian query()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdExperian whereBatchNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdExperian whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdExperian whereExtraInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdExperian whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdExperian whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdExperian whereReportBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdExperian whereRequestInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdExperian whereResponseInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdExperian whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdExperian whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdExperian whereType($value)
 * @mixin \Eloquent
 */
class ThirdExperian extends RiskBaseModel
{
    /** @var string 类型：Experian沙箱环境 */
    const TYPE_EXPERIAN_SANDBOX = 'experian_sandbox';
    /** @var string 类型：Experian 根据sql跑批 */
    const TYPE_EXPERIAN = 'experian';
    public $timestamps = false;
    protected $table = 'third_experian';
    protected $fillable = [];
    protected $guarded = [];

    public function textRules()
    {
        return [];
    }
}
