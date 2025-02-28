<?php

namespace Common\Models\Merchant;

use Common\Models\Staff\Staff;
use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Yunhan\Utils\Env;

/**
 * Common\Models\Merchant\Merchant
 *
 * @property int $id
 * @property string $merchant_name
 * @property int $status 商户状态 1：正常 2：禁用  -1：删除
 * @property float $balance 商户余额
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\Merchant whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\Merchant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\Merchant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\Merchant whereMerchantName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\Merchant whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\Merchant whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $merchant_no 商户唯一编号
 * @property string $product_name 产品名
 * @property string $contact 联系人姓名
 * @property string $contact_telephone 联系人手机号
 * @property string $contact_email 联系人邮箱
 * @property string $contract_sign_date 合同签署日期
 * @property string $contract_expiry_date 合同到期日
 * @property string $admin_nick 关联商务(创建商户的管理员账号)
 * @property string|null $type
 * @property string|null $app_key 印牛服务app key
 * @property string|null $app_secret_key 印牛服务app秘钥
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\Merchant whereAdminNick($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\Merchant whereAppKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\Merchant whereAppSecretKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\Merchant whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\Merchant whereContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\Merchant whereContactTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\Merchant whereContractExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\Merchant whereContractSignDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\Merchant whereMerchantNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\Merchant whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\Merchant whereType($value)
 * @property string|null $lender 商户NBFC名称
 * @method static Builder|Merchant newModelQuery()
 * @method static Builder|Merchant newQuery()
 * @method static Builder|Merchant orderByCustom($defaultSort = null)
 * @method static Builder|Merchant query()
 * @method static Builder|Merchant whereLender($value)
 */
class Merchant extends Model
{
    use StaticModel;

    const SCENARIO_CREATE = 'create';

    /** @var int 状态：正常 */
    const STATUS_NORMAL = 1;
    /** @var int 状态：禁用 */
    const STATUS_DISABLE = 2;
    /** @var int 状态：删除 */
    const STATUS_DELETE = -1;
    /** @var array 状态 */
    const STATUS = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_DISABLE => '禁用',
        self::STATUS_DELETE => '删除',
    ];
    
    
    public static function getId($prodName){
        if(Env::isProd()){
            switch($prodName){
                case "u1":
                    return 3;
                case "u3":
                    return 6;
                case "u4":
                    return 2;
                case "u5":
                    return 1;
                case "c1":
                    return 5;
                case "s2":
                    return 4;
            }
        }else{
            switch($prodName){
                case "u1":
                    return 3;
                case "u3":
                    return 6;
                case "u4":
                    return 2;
                case "u5":
                    return 1;
                case "c1":
                    return 5;
                case "s2":
                    return 4;
            }
        }
        return 0;
    }

    // 此属性不能删除，跨库关联时必须定义
    protected $connection = 'mysql';

    protected $table = 'merchant';

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope('valid', function (Builder $builder) {
            $builder->where('status', '!=', self::STATUS_DELETE);
        });
    }

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'id',
                'merchant_no',
                'merchant_name',
                'product_name',
                'contact',
                'contact_telephone',
                'contact_email',
                'contract_sign_date',
                'contract_expiry_date',
                'status',
                'balance',
                'admin_nick',
                'app_key',
                'app_secret_key',
                'type',
            ],
        ];
    }

    public function getNormalAll($ids = null)
    {
        $query = $this->newQuery()->where('status', self::STATUS_NORMAL);

        if (isset($ids)) {
            $query->whereIn('id', (array)$ids);
        }

        return $query->orderByDesc('id')->get();
    }

    /**
     * 根据 id 获取状态正常的 merchant
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder|Model|object|null|static
     */
    public function getNormalById($id)
    {
        $where = [
            'id' => $id,
            'status' => self::STATUS_NORMAL,
        ];

        return $this->newQuery()
            ->where($where)
            ->first();
    }

    public function getById($id)
    {
        return $this->newQuery()->where('id', $id)->first();
    }

    /**
     * @param $params
     * @return Merchant|bool
     */
    public function add($params)
    {
        $data = array_only($params, [
            'id',
            'merchant_name',
            'product_name',
            'contract_sign_date',
            'contract_expiry_date',
            'contact',
            'contact_telephone',
            'contact_email',
            'admin_nick',
            'app_key',
            'app_secret_key',
            'merchant_no',
            'status',
        ]);
        return self::model(self::SCENARIO_CREATE)->saveModel($data);
    }

    /**
     * 关联 app 表
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function app($class = App::class)
    {
        return $this->hasMany($class, 'merchant_id', 'id');
    }

    public function staff($class = Staff::class)
    {
        return $this->hasMany($class, 'merchant_id', 'id')
            ->where('status', Staff::STATUS_NORMAL);
    }

    public function superAdmin($class = Staff::class)
    {
        return $this->hasOne($class, 'merchant_id', 'id')->where([
            'is_super' => Staff::IS_SUPER_YES,
        ]);
    }

    /**
     * 获取自增MerchantID
     * @return int|mixed
     */
    public function getNewMerchantId()
    {
        $lastId = $this->newQuery()->orderByDesc('id')->value('id') ?? 0;
        return $lastId + 1;
    }

    /**
     * 生成商户唯一编号
     * @param $prefix
     * @return string
     */
    public static function generateMerchantNo($prefix = null)
    {
        $prefix = $prefix ?: 'SH';
        $partnerId = $prefix . date('YmdHis') . mt_rand(10, 100);
        if (self::model()->getByMerchantNo($partnerId)) {
            return self::generateMerchantNo($prefix);
        }

        return $partnerId;
    }

    public function getByMerchantNo($partnerNo)
    {
        return self::where('merchant_no', $partnerNo)->first();
    }

    /**
     * @param $appKey
     * @return self|null
     */
    public static function getByAppKey($appKey)
    {
        return self::query()->where('app_key', $appKey)->first();
    }
    
    public function getIdByName($name){
        return self::query()->where("product_name", $name)->first()->id;
    }
}
