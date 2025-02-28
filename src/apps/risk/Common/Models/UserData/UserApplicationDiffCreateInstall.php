<?php

namespace Risk\Common\Models\UserData;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\RiskData\HighRiskPhonenumber
 *
 * @property int $id
 * @property string $created_at 创建时间
 * @mixin \Eloquent
 */
class UserApplicationDiffCreateInstall extends RiskBaseModel
{
    /**
     * @var string
     */
    protected $table = 'risk_user_application_diff_create_install';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    protected $guarded = [];
}
