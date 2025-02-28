<?php

namespace Risk\Common\Models\Third;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\Third\ThirdDataWhatsapp
 *
 * @property int $id
 * @property string|null $telephone 电话号码
 * @property string|null $webhook_data What'spp接口返回
 * @property int|null $wa 是否whatsapp1是,2否
 * @property int|null $stop_count 阻拦次数
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataWhatsapp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataWhatsapp newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataWhatsapp query()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataWhatsapp whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataWhatsapp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataWhatsapp whereStopCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataWhatsapp whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataWhatsapp whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataWhatsapp whereWa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataWhatsapp whereWebhookData($value)
 * @mixin \Eloquent
 */
class ThirdDataWhatsapp extends RiskBaseModel {

    protected $table = 'third_data_whatsapp';
    protected $fillable = [];
    protected $guarded = [];

    const STATUS_SUCCESS = 1; //验证通过
    const STATUS_FAILED = 2; //验证失败
    const STATUS_SKIP = 3; //验证跳过
    const LIFT_DAY = 3; //3天有效记录有效期天数

    public $areaCode = "91";

    public function textRules() {
        return [];
    }

    /**
     * 查找手机号是否命中whatsapp规则
     * @param type $telephone
     * @return type
     */
    public function check($telephone) {
        $query = $this->findByTelephone($telephone);
        if ($query) {
            $query->increment('stop_count');
            if ($query->wa == "2") {
                return self::STATUS_FAILED;
            }
            $data = json_decode($query->webhook_data, true);
            //活跃时间策略
            if (isset($data['lastSeen'])) {
                $timeDiffDay = ceil((time() - strtotime($data['lastSeen'])) / (3600 * 24));
                if ($timeDiffDay > 5) {
                    return self::STATUS_FAILED;
                }
            }
            //头像时间策略
            if (isset($data['headTime'])) {
                $timeDiffDay = ceil((time() - strtotime($data['headTime'])) / (3600 * 24));
                if ($timeDiffDay > 90 && $timeDiffDay < 210) {
                    return self::STATUS_FAILED;
                }
            }

            //签名时间2019年的
            if (isset($data['signTime'])) {
                if (date("Y", strtotime($data['signTime'])) == "2019") {
                    return self::STATUS_FAILED;
                }
            }
            return self::STATUS_SUCCESS;
        }
        return self::STATUS_SKIP;
    }

    public function findByTelephone($telephone) {
        $telephone = $this->areaCode . $telephone;
        $liftTime = date("Y-m-d H:i:s", strtotime("-" . $this::LIFT_DAY . "day"));
        return self::query()->where("telephone", "=", $telephone)->where("created_at", ">=", $liftTime)->orderBy('id', 'desc')->first();
    }

    //确认是否是wa用户
    public function simpleCheck($telephone) {
        $telephone = $this->areaCode . $telephone;
        $res = self::query()->where("telephone", "=", $telephone)->orderBy('id', 'desc')->first();
        if ($res) {
            $res->increment('stop_count');
            if ($res->wa == '1') {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

}
