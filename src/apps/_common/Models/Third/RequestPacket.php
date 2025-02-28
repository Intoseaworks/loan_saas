<?php

namespace Common\Models\Third;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Third\RequestPacket
 *
 * @property int $id 自增ID
 * @property int $relate_id 关联ID
 * @property string $relate_type 关联类型
 * @property int $type 报文类型
 * @property string|null $url 请求url
 * @property string|null $request_info 请求报文
 * @property string|null $response_info 响应报文
 * @property string|null $http_code http状态码
 * @property string|null $remark 备注
 * @property \Illuminate\Support\Carbon $updated_at 更新时间
 * @property \Illuminate\Support\Carbon $created_at 添加时间
 * @method static \Illuminate\Database\Eloquent\Builder|RequestPacket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestPacket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestPacket orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestPacket query()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestPacket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestPacket whereHttpCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestPacket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestPacket whereRelateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestPacket whereRelateType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestPacket whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestPacket whereRequestInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestPacket whereResponseInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestPacket whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestPacket whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestPacket whereUrl($value)
 * @mixin \Eloquent
 */
class RequestPacket extends Model
{
    use StaticModel;

    protected $table = 'request_packet';
    protected $fillable = [];
    protected $guarded = [];

    /** @var int 关联类型：请求HighMark征信报告 */
    const RELATE_TYPE_REQUEST_HM_CREDIT_REPORT = 'REQUEST_HM_CREDIT_REPORT';

    /** @var string 关联类型：请求experian征信报告 */
    const RELATE_TYPE_REQUEST_EXPERIAN_CREDIT_REPORT = 'REQUEST_EXPERIAN_CREDIT_REPORT';

    /** @var string 关联类型: experian征信报告跑批 */
    const RELATE_TYPE_REQUEST_EXPERIAN_CREDIT_REPORT_BATCH = 'REQUEST_EXPERIAN_CREDIT_REPORT_BATCH';

    /** @var string 类型：请求报文 */
    const TYPE = 1;

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function textRules()
    {
        return [];
    }

    public static function addHighMarkRequestPacket($orderId, $url, $requestInfo, $responseInfo, $httpCode = 200, $remark = '')
    {
        $packetAttribute = [
            'relate_id' => $orderId,
            'relate_type' => self::RELATE_TYPE_REQUEST_HM_CREDIT_REPORT,
            'url' => $url,
            'request_info' => $requestInfo,
            'response_info' => $responseInfo,
            'http_code' => $httpCode,
            'remark' => $remark,
        ];
        return RequestPacket::create($packetAttribute);
    }

    public static function addExperianRequestPacket($orderId, $url, $requestInfo, $responseInfo, $httpCode = 200, $remark = '')
    {
        $packetAttribute = [
            'relate_id' => $orderId,
            'relate_type' => self::RELATE_TYPE_REQUEST_EXPERIAN_CREDIT_REPORT,
            'url' => $url,
            'request_info' => $requestInfo,
            'response_info' => $responseInfo,
            'http_code' => $httpCode,
            'remark' => $remark,
        ];
        return RequestPacket::create($packetAttribute);
    }
}
