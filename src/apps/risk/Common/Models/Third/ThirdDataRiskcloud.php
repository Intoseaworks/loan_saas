<?php

namespace Risk\Common\Models\Third;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\Third\ThirdDataRiskcloud
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataRiskcloud newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataRiskcloud newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataRiskcloud query()
 * @mixin \Eloquent
 */
class ThirdDataRiskcloud extends RiskBaseModel {

    protected $table = 'third_data_riskcloud';
    protected $fillable = [];
    protected $guarded = [];

    const STATUS_SUCCESS = 1; //验证通过
    const STATUS_FAILED = 2; //验证失败
    const TYPE_FACE_MATCH = "faceMatch";
    const TYPE_PAN_VERIFY = "panVerify";
    const TYPE_AADHAAR_VERIFY = "aadhaarVerify";
    const TYPE_BANK_CARD_VERIFY = "bankCardVerify";
    const TYPE_SEARCH_MULTI_LOAN = "searchMultiLoan";
    const TYPE_SEARCH_BLACK = "searchBlack";

    public function textRules() {
        return [];
    }

    public function findByUserType($uid, $type) {
        return self::query()->where("user_id",$uid)->where("status", "=", self::STATUS_SUCCESS)->where("type", "=", $type)->first();
    }

}
