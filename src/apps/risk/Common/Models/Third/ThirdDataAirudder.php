<?php

namespace Risk\Common\Models\Third;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Risk\Common\Models\Third\ThirdDataAirudder
 *
 * @property int $id
 * @property int $merchant_id 商户id
 * @property string|null $telephone
 * @property string|null $webhook_data
 * @property string|null $status 号码状态0=未知;1无效号码;2有效号码;3正忙;4号码无法使用（欠费）5呼叫保持;6关机;7不在服务区;8号码未注册激活;9号码无效或错误号码
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $init_request 发起-请求
 * @property string|null $init_response 发起-响应
 * @property string|null $order_id 发起-请求ID
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAirudder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAirudder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAirudder query()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAirudder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAirudder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAirudder whereInitRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAirudder whereInitResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAirudder whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAirudder whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAirudder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAirudder whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAirudder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAirudder whereWebhookData($value)
 * @mixin \Eloquent
 */
class ThirdDataAirudder extends Model
{

    use StaticModel;

    protected $table = 'third_data_airudder';
    protected $fillable = [];
    protected $guarded = [];
    const AREA_CODE = "+63";

    public function textRules()
    {
        return [];
    }

    public function check($telephone) {
        $query = $this->findByTelephone($telephone);
        if ($query) {
            switch ($query->status) {
                case "4":
                case "6":
                case "7":
                case "8":
                case "9":
                    return true;
                default:
                    return false;
            }
        }

        return null;
    }
    
    public function checkTime($telephone) {
        $query = $this->findByTelephone($telephone);
        if ($query) {
            return $query->updated_at;
        }

        return null;
    }

    public function findByTelephone($telephone) {
        $telephone = self::AREA_CODE . $telephone;
        return self::query()->where("telephone", "=", $telephone)->orderBy('id', 'desc')->first();
    }

}
