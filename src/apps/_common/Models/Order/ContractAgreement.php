<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/14
 * Time: 23:44
 */

namespace Common\Models\Order;


use Common\Services\OrderAgreement\IntermediaryAgreementTemplate;
use Common\Services\OrderAgreement\LoanAgreementTemplate;
use Common\Services\OrderAgreement\RenewalAgreementTemplate;
use Common\Traits\Model\StaticModel;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Upload\ImageHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Order\ContractAgreement
 *
 * @property int $id
 * @property int|null $contract_id 合同id
 * @property string|null $name
 * @property string|null $url
 * @property int|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\ContractAgreement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\ContractAgreement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\ContractAgreement query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\ContractAgreement whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\ContractAgreement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\ContractAgreement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\ContractAgreement whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\ContractAgreement whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\ContractAgreement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\ContractAgreement whereUrl($value)
 * @mixin \Eloquent
 * @property int|null $order_id 合同id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\ContractAgreement orderByCustom($column = null, $direction = 'asc')
 * @property string|null $fix_json 合同中固定不能更改的字段值
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\ContractAgreement whereFixJson($value)
 */
class ContractAgreement extends Model
{
    use StaticModel;

    /** 状态：正常 */
    const STATUS_ACTIVE = 1;
    /** 状态：失效 */
    const STATUS_QUIET = 2;
    /** 状态：已删除 */
    const STATUS_DELETE = -1;
    /** 状态 */
    const STATUS = [
        self::STATUS_ACTIVE => '正常',
        self::STATUS_QUIET => '失效',
        self::STATUS_DELETE => '已删除',
    ];

    const SCENARIO_LIST = 'list';
    const SCENARIO_DETAIL = 'detail';

    const SCENARIO_CREATE = 'create';

    const LOAN_AGREEMENT = '借款协议';
    const INTERMEDIARY_AGREEMENT = '居间服务协议';
    const RENEWAL_AGREEMENT = '续期协议';
    const CASHNOW_LOAN_CONTRACT = 'cashnow借款合同';

    const TYPE = [
        self::LOAN_AGREEMENT => '借款协议',
        self::INTERMEDIARY_AGREEMENT => '居间服务协议',
        self::RENEWAL_AGREEMENT => '续期协议',
        self::CASHNOW_LOAN_CONTRACT => '借款合同',
    ];

    const NEED_AUTO_TYPE = [
        self::CASHNOW_LOAN_CONTRACT,
    ];

    /** 更新时固定字段 */
    const FIX_FIELD = [
        self::LOAN_AGREEMENT => LoanAgreementTemplate::FIX_FIELD,
        self::INTERMEDIARY_AGREEMENT => IntermediaryAgreementTemplate::FIX_FIELD,
        self::RENEWAL_AGREEMENT => RenewalAgreementTemplate::FIX_FIELD,
    ];

    /**
     * @var string
     */
    protected $table = 'contract_agreement';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'order_id',
                'name',
                'url',
                'fix_json',
                'status' => self::STATUS_ACTIVE
            ]
        ];
    }

    public function texts()
    {
        return [
            self::SCENARIO_LIST => [
                'id',
                'name',
                'url',
                'created_at',
            ],
            self::SCENARIO_DETAIL => [
                'id',
            ]
        ];
    }

    public function textRules()
    {
        return [
            'array' => [
                'name' => ts(self::TYPE, 'order'),
            ],
            'function' => [
                'url' => function () {
                    return ImageHelper::getPicUrl($this->url, 0);
                }
            ],
        ];
    }

    public function getAgreementByOrderId($orderId)
    {
        return self::query()->whereOrderId($orderId)->whereStatus(self::STATUS_ACTIVE)->first();
    }

    public function getAgreementsByOrderId($orderId)
    {
        return self::query()->whereOrderId($orderId)->whereStatus(self::STATUS_ACTIVE)->get();
    }

    public function toQuietByType($orderId, $type)
    {
        $where = [
            'order_id' => $orderId,
            'name' => $type,
        ];
        return self::where($where)->update(['status' => self::STATUS_QUIET]);
    }

    public function add($orderId, $path, $type, $data = [])
    {
        $getFixStr = array_only($data, array_get(self::FIX_FIELD, $type, []));
        $attribute = [
            'order_id' => $orderId,
            'url' => $path,
            'name' => $type,
        ];
        if ($getFixStr) {
            $attribute['fix_json'] = ArrayHelper::arrayToJson($getFixStr);
        }
        return ContractAgreement::model()->setScenario(ContractAgreement::SCENARIO_CREATE)->saveModel($attribute);
    }

    /**
     * 更新协议时，将不可更改字段替换为之前协议中存储的值
     * @param $orderId
     * @param $type
     * @param array $data
     * @return array|bool
     */
    public function replaceFixByType($orderId, $type, $data = [])
    {
        $where = [
            'order_id' => $orderId,
            'name' => $type,
            'status' => self::STATUS_ACTIVE,
        ];
        $model = self::query()->where($where)
            ->orderBy('id', 'desc')
            ->first();

        if (!$model) {
            return $data;
        }
        $fixArr = json_decode($model->fix_json, true) ?? [];
        return array_merge($data, $fixArr);
    }

    /**
     * @param $id
     * @return $this|Model|object|null
     */
    public function getOne($id)
    {
        return self::whereId($id)->first();
    }
}
