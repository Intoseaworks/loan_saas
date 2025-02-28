<?php

namespace Common\Models\NewClm;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Common\Models\NewClm\ClmCustomer
 *
 * @property int $id id
 * @property string $clm_customer_id 客户clm_id
 * @property string $idcard_type 证件类型
 * @property string $idcard 身份证号
 * @property string $name 姓名
 * @property string $phone 手机号
 * @property string $birthday 生日
 * @property string $sex 性别
 * @property int|null $max_level 该客户最大等级
 * @property string $total_amount 总额度--该客户在所有商户下的总额度，由max_level与等级额度匹配表匹配获取的结果
 * @property string $used_amount 已用额度--该客户在所有商户的合计已使用额度：所有未结清订单的合同金额之和
 * @property string $freeze_amount 冻结额度--该客户在所有商户的合计冻结额度：所有已签约但未放款订单的合同金额之和
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Common\Models\NewClm\ClmCustomerExt|null $ext
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereClmCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereFreezeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereIdcard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereIdcardType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereMaxLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereUsedAmount($value)
 * @mixin \Eloquent
 */
class ClmCustomer extends Model
{
    use StaticModel;

    protected $table = 'new_clm_customer';

    protected $guarded = [];

    /*************************************************************************
     * relations
     ************************************************************************/

    /**
     * 关联子表
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ext()
    {
        return $this->hasOne(ClmCustomerExt::class, 'clm_customer_id', 'clm_customer_id');
    }

    /*************************************************************************
     * attributes
     ************************************************************************/

    /**
     * 是否正常状态
     *
     * @return bool
     */
    public function isNormal(): bool
    {
        return $this->ext->status == ClmCustomerExt::STATUS_NORMAL;
    }

    /**
     * 是否冻结状态
     *
     * @return bool
     */
    public function isFrozen(): bool
    {
        return $this->ext->status == ClmCustomerExt::STATUS_FROZEN;
    }

    /**
     * 获取客户等级
     *
     * @return mixed
     */
    public function getLevel()
    {
        return $this->ext->current_level ?? null;
    }

    /**
     * 获取用户等级别名
     * @return mixed|string
     */
    public function getLevelAlias()
    {
        return $this->getCurrentLevelAmount()->alias ?? 'BRONZE';
    }

    /**
     * 根据 current_level 获取对应金额配置
     *
     * @return ClmAmount|null
     */
    public function getCurrentLevelAmount(): ?ClmAmount
    {
        $level = $this->getLevel();

        return ClmAmount::getAutoByLevel($level);
    }

    /**
     * 计算可贷额度
     *
     * @return float
     */
    public function calcAvailableAmount(): float
    {
        $availableAmount = bcsub(bcsub($this->total_amount, $this->freeze_amount, 2), $this->used_amount, 2);

        $clmAmount = $this->getCurrentLevelAmount()->clm_amount;
        #u3项目直接返回clmamount
        if(\Common\Utils\MerchantHelper::getMerchantId() == 6){
            return $clmAmount;
        }

        // 最少1000，可能会出现 used_amount + freeze_amount 超过 total_amount 的情况
        return (float)max(min($availableAmount, $clmAmount), ClmAmount::MIN_CLM_AMOUNT);
    }

    /*************************************************************************
     * 状态流转
     ************************************************************************/

    /**
     * 冻结
     * @param $amount
     *
     * @return bool
     * @throws \Throwable
     */
    public function freeze($amount): bool
    {
        #哥大项目不做冻结与解冻
        if(\Common\Utils\MerchantHelper::getMerchantId() == \Common\Models\Merchant\Merchant::getId("c1")){
            return true;
        }
        #u3项目不做冻结与解冻
        if(\Common\Utils\MerchantHelper::getMerchantId() == 6){
            return true;
        }
        $availableAmount = $this->calcAvailableAmount();
        if ($amount > $availableAmount) {
            return false;
        }

        $update = [
            'freeze_amount' => bcadd($this->freeze_amount, $amount, 2),
        ];

        $res = DB::transaction(function () use ($update) {
            $this->ext->freeze();

            return $this->update($update);
        });

        return $res;
    }

    /**
     * 解冻
     *
     * @param $amount
     *
     * @return bool
     * @throws \Exception
     */
    public function unfreeze($amount, $updateStatus = true): bool
    {
        #哥大项目不做冻结与解冻
        if(\Common\Utils\MerchantHelper::getMerchantId() == \Common\Models\Merchant\Merchant::getId("c1")){
            return true;
        }
        #u3项目不做冻结与解冻
        if(\Common\Utils\MerchantHelper::getMerchantId() == 6){
            return true;
        }
        if (!$this->isFrozen()) {
            return false;
        }

        $update = [
            'freeze_amount' => max(bcsub($this->freeze_amount, $amount, 2), 0),
        ];

        $res = DB::transaction(function () use ($update, $updateStatus) {

            $res = $this->update($update);

            // 冻结金额为0时，解冻
//            if ($this->freeze_amount == 0) {
            if($updateStatus) {
                $this->ext->unfreeze();
            }
//            }

            return $res;
        });

        return $res;
    }

    /**
     * 使用额度
     *
     * @param $amount
     *
     * @return bool
     * @throws \Throwable
     */
    public function use($amount): bool
    {
        #哥大项目不做冻结与解冻
        if(\Common\Utils\MerchantHelper::getMerchantId() == \Common\Models\Merchant\Merchant::getId("c1")){
            return true;
        }
        #u3项目不做冻结与解冻
        if(\Common\Utils\MerchantHelper::getMerchantId() == 6){
            return true;
        }
        if (!$this->isFrozen()) {
            return false;
        }

        $update = [
            'freeze_amount' => max(bcsub($this->freeze_amount, $amount, 2), 0),
            'used_amount' => bcadd($this->used_amount, $amount, 2),
        ];

        $res = DB::transaction(function () use ($update) {

            $res = $this->update($update);

            // 冻结金额为0时，解冻
//            if ($this->freeze_amount == 0) {
                $this->ext->unfreeze();
//            }

            return $res;
        });

        return $res;
    }

    /**
     * 完成
     *
     * @param $adjustLevel
     * @param $amount
     *
     * @return bool
     * @throws \Throwable
     */
    public function finish($adjustLevel, $amount): bool
    {
        #哥大项目不做冻结与解冻
        if(\Common\Utils\MerchantHelper::getMerchantId() == \Common\Models\Merchant\Merchant::getId("c1")){
            return true;
        }
        $res = DB::transaction(function () use ($adjustLevel, $amount) {

            $this->ext->changeLevel($adjustLevel);

            #u3项目不做冻结与解冻
            if(\Common\Utils\MerchantHelper::getMerchantId() == 6){
                return true;
            }
            $maxLevel = ClmCustomerExt::getMaxLevel($this->clm_customer_id);
            $totalAmount = ClmAmount::getAmountByLevel($maxLevel);

            $update = [
                'used_amount' => max(bcsub($this->used_amount, $amount, 2), 0),
                //'max_level' => $maxLevel,
                //'total_amount' => $totalAmount,
            ];

            $res = $this->update($update);

            // 冻结金额为0时，解冻
//            if ($this->freeze_amount == 0) {
                $this->ext->unfreeze();
//            }

            return $res;
        });

        return $res;
    }

    /*************************************************************************
     * static
     ************************************************************************/

    /**
     * 根据客户ID获取客户
     *
     * @param $customerId
     *
     * @return static|null
     */
    public static function getByCustomerId($customerId)
    {
        return self::query()->where('clm_customer_id', $customerId)->first();
    }

    /**
     * 生成客户ID
     *
     * @return string
     */
    public static function generateCustomerId()
    {
        $prefix = (string)mt_rand(1, 99999);
        $id = '11' . strtoupper(substr(uniqid($prefix), 0, 16));
        if (self::getByCustomerId($id)) {
            return self::generateCustomerId();
        }
        return $id;
    }
}
