<?php

namespace Common\Models\Nbfc;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Nbfc\NbfcReportConfig
 *
 * @property int $id
 * @property int $merchant_id
 * @property string $platform 平台
 * @property string $config 配置 json
 * @property int $status 状态
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 修改时间
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportConfig orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportConfig whereConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportConfig whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportConfig whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportConfig wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportConfig whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportConfig whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NbfcReportConfig extends Model
{
    use StaticModel;

    protected $table = 'nbfc_report_config';

    /** 平台：Vishnupriya */
    const PLATFORM_VISHNUPRIYA = 'vishnupriya';
    const PLATFORM_KUDOS = 'kudos';
    /** 平台 */
    const PLATFORM = [
        self::PLATFORM_VISHNUPRIYA => 'Vishnupriya',
        self::PLATFORM_KUDOS => 'Kudos',
    ];

    const STATUS_NORMAL = 1;
    const STATUS_DISABLE = 2;
    const STATUS_DELETE = -1;
    const STATUS = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_DISABLE => '禁用',
        self::STATUS_DELETE => '删除',
    ];

    /** 上报节点：订单签约 */
    const REPORT_NODE_SIGN = 'sign';
    /** 上报节点：放款成功 */
    const REPORT_NODE_REMIT = 'remit';
    /** 上报节点：还款成功 */
    const REPORT_NODE_REPAY = 'repay';

    /**
     * 平台对应需要上传的节点
     */
    const PLATFORM_NEED_REPORT_NODE = [
        self::PLATFORM_VISHNUPRIYA => [
            self::REPORT_NODE_SIGN,
            self::REPORT_NODE_REMIT,
            self::REPORT_NODE_REPAY,
        ],
        self::PLATFORM_KUDOS => [
            self::REPORT_NODE_SIGN,
            self::REPORT_NODE_REMIT,
            self::REPORT_NODE_REPAY,
        ],
    ];

    /**
     * 可修改订单状态为pass的节点
     */
    const PLATFORM_PASS_NODE = [
        self::PLATFORM_VISHNUPRIYA => self::REPORT_NODE_SIGN,
        self::PLATFORM_KUDOS => self::REPORT_NODE_SIGN,
    ];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function textRules()
    {
        return [];
    }

    const SCENARIO_CREATE = 'create';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id',
                'platform',
                'config',
            ]
        ];
    }

    public static function getConfig($merchantId)
    {
        $where = [
            'merchant_id' => $merchantId,
            'status' => self::STATUS_NORMAL,
        ];

        return self::query()->where($where)->first();
    }
}
