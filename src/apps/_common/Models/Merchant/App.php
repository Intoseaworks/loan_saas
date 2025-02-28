<?php

namespace Common\Models\Merchant;

use Common\Traits\Model\StaticModel;
use Common\Utils\Data\ArrayHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Merchant\App
 *
 * @property int $id
 * @property int $merchant_id 商户id
 * @property string $app_key app唯一key
 * @property string $app_name app名称
 * @property string|null $page_name 包名
 * @property int $status 状态 1：正常  2：禁用  -1：删除
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $google_server_key google服务key
 * @property string|null $app_url app下载地址
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\App whereAppKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\App whereAppName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\App whereAppUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\App whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\App whereGoogleServerKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\App whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\App whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\App wherePageName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\App whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Merchant\App whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $send_id 短信发信ID
 * @property string|null $putaway_time 上架时间
 * @method static Builder|App newModelQuery()
 * @method static Builder|App newQuery()
 * @method static Builder|App orderByCustom($defaultSort = null)
 * @method static Builder|App query()
 * @method static Builder|App wherePutawayTime($value)
 * @method static Builder|App whereSendId($value)
 */
class App extends Model
{
    use StaticModel;

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
    protected $table = 'app';

    protected $guarded = [];

    public static $idNameArr;

    public static $idDataArr;

    public static function boot()
    {
        parent::boot();

        self::setMerchantIdBootScope();

        static::addGlobalScope('valid', function (Builder $builder) {
            $builder->where('status', '!=', self::STATUS_DELETE);
        });
    }

    /**
     * 根据 app_id 获取具有相同归属的 app_ids
     * @param $appId
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllSameBelong($appId)
    {
        return $this->newQuery()->where('merchant_id', function ($query) use ($appId) {
            $query->select('merchant_id')->from('app')->where('id', $appId);
        })->get();
    }

    public function getFirstAppByMerchantId($merchantId)
    {
        $where = [
            'merchant_id' => $merchantId,
            'status' => self::STATUS_NORMAL,
        ];

        return $this->newQuery()->where($where)->first();
    }

    /**
     * 根据 app_key 查找状态正常的app
     * @param $appKey
     * @return Builder|Model|object|null
     */
    public function getNormalAppByKey($appKey)
    {
        $where = [
            'app_key' => $appKey,
            'status' => self::STATUS_NORMAL,
        ];

        return $this->newQuery()
            ->where($where)
            ->whereHas('merchant', function (Builder $query) {
                $query->where('status', Merchant::STATUS_NORMAL);
            })
            ->first();
    }

    /**
     * 根据 app_id 查找状态正常的 app
     * @param $id
     * @return Builder|Model|object|null
     */
    public function getNormalAppById($id)
    {
        $where = [
            'id' => $id,
            'status' => self::STATUS_NORMAL,
        ];

        return $this->newQuery()
            ->where($where)
            ->whereHas('merchant', function (Builder $query) {
                $query->where('status', Merchant::STATUS_NORMAL);
            })
            ->first();
    }

    /**
     * 生成唯一 app_key
     * @param $prefix
     * @return string
     */
    public static function generateAppKey($prefix = null)
    {
        $prefix = $prefix ?: 'APP';
        $appKey = strtoupper(uniqid($prefix)) . mt_rand(99, 999);
        if (self::model()->getByAppKey($appKey)) {
            return self::generateAppKey($prefix);
        }

        return $appKey;
    }

    public function getByAppKey($appKey)
    {
        return self::where('app_key', $appKey)->first();
    }

    public function getByAppName($appName)
    {
        return self::where('app_name', $appName)->first();
    }

    public function getByMerchantId($merchantId)
    {
        return self::where('merchant_id', $merchantId)->first();
    }

    public function getByPageName($pageName)
    {
        return self::where('page_name', $pageName)->first();
    }

    public function add($merchantId, $appName)
    {
        $data = [
            'merchant_id' => $merchantId,
            'app_key' => self::generateAppKey(),
            'app_name' => $appName,
            'status' => self::STATUS_NORMAL,
        ];

        return $this->create($data);
    }

    public function getAll($name, $key = 'id')
    {
        return self::whereIn('status', [self::STATUS_NORMAL, self::STATUS_DISABLE])->pluck($name, $key);
    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getNormal()
    {
        $query = $this->newQuery()->where('status', self::STATUS_NORMAL);
        return $query->get();
    }

    public static function getIdNameArr()
    {
        if(static::$idNameArr){
            return static::$idNameArr;
        }
        static::$idNameArr = App::model()->getAll('app_name')->toArray();
        return static::$idNameArr;
    }

    /**
     * 获取对应app的值
     * @return mixed
     */
    public static function getDataById($appId, $name, $default = '')
    {
        if(!static::$idDataArr || !is_array(static::$idDataArr)){
            static::$idDataArr = ArrayHelper::arrayChangeKey((new App)->query()->withoutGlobalScope(self::$bootScopeMerchant)->get()->toArray(), 'id');
        }
        return array_get(static::$idDataArr, $appId.'.'.$name, $default);
    }

    public static function getData()
    {
        return static::$idDataArr;
    }

    /**
     * 关联 merchant 表
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function merchant($class = Merchant::class)
    {
        return $this->belongsTo($class, 'merchant_id', 'id');
    }
}
