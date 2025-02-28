<?php

namespace Risk\Common\Models\HighMark;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\HighMark\HmInquriyHistDetail
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id
 * @property int $relate_id
 * @property string|null $report_id REPORT-ID
 * @property string|null $inqury_no inqury_no 用于区分同分报告下的记录
 * @property string|null $member_name MEMBER-NAME
 * @property string|null $inquiry_date INQUIRY-DATE
 * @property string|null $purpose PURPOSE
 * @property string|null $ownership_type OWNERSHIP-TYPE
 * @property string|null $amount AMOUNT
 * @property string|null $remark REMARK
 * @property string $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|HmInquriyHistDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HmInquriyHistDetail newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInquriyHistDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|HmInquriyHistDetail whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInquriyHistDetail whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInquriyHistDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInquriyHistDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInquriyHistDetail whereInquiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInquriyHistDetail whereInquryNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInquriyHistDetail whereMemberName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInquriyHistDetail whereOwnershipType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInquriyHistDetail wherePurpose($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInquriyHistDetail whereRelateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInquriyHistDetail whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInquriyHistDetail whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInquriyHistDetail whereUserId($value)
 * @mixin \Eloquent
 */
class HmInquriyHistDetail extends RiskBaseModel
{
    public $table = 'hm_inquriy_hist_detail';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [];
    }
}
