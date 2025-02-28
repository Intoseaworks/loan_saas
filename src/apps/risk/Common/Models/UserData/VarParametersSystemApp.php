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
class VarParametersSystemApp extends RiskBaseModel
{
    /**
     * @var string
     */
    protected $table = 'var_parameters_system_app';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    protected $guarded = [];
}
