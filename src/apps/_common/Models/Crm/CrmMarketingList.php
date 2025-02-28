<?php

/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/1/29
 * Time: 16:08
 */

namespace Common\Models\Crm;

use Common\Traits\Model\StaticModel;
use Common\Models\Crm\CrmWhiteBatch;
use Common\Models\Crm\CustomerStatus;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Crm\CrmMarketingList
 *
 * @property int $id
 * @property int|null $batch_id 批次号
 * @property string|null $telephone 手机号
 * @property string|null $type 白名单类型
 * @property string|null $email email
 * @property string|null $fullname 全名
 * @property string|null $birthday 生日
 * @property string|null $id_type 证件类型
 * @property string|null $id_number 证件号码
 * @property string|null $remark 备注
 * @property int|null $status 状态1=正常2=失败
 * @property string|null $indate 有效期null=不限期限
 * @property int|null $admin_id 操作人ID（staff.id）
 * @property int|null $customer_id crmCustomerID
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList whereIdNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList whereIdType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList whereIndate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingList whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CrmMarketingList extends Model {

    use StaticModel;

    const STATUS_NORMAL = 1; //正常
    const STATUS_FORGET = 0; //失效
    const TYPE_MARKETING = 1; //首贷-营销
    const TYPE_WHITELIST = 2; //首贷-白名单
    const TYPE_GENERAL = 3; //首贷-一般用户
    const TYPE_RELOAN = 4; //首贷-复贷
    const TYPE_LIST = [
        self::TYPE_MARKETING => "首贷-营销名单",
        self::TYPE_WHITELIST => "首贷-白名单",
        self::TYPE_GENERAL => "首贷-一般用户",
        self::TYPE_RELOAN => "首贷-复贷",
    ];
    const TYPE_MAP = [
        "首贷-营销名单" => self::TYPE_MARKETING,
        "首贷-白名单" => self::TYPE_WHITELIST,
        "首贷-一般用户" => self::TYPE_GENERAL,
        "首贷-复贷" => self::TYPE_RELOAN,
    ];
    const STATUS = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_FORGET => '失效'
    ];

    protected $table = 'crm_marketing_list';

    public function crmMarketingBatch($class = CrmMarketingBatch::class) {
        return $this->hasOne($class, 'id', 'batch_id')->orderBy('id', 'desc');
    }

    public function customer($class = Customer::class) {
        return $this->hasOne($class, 'id', 'customer_id')->orderBy('id', 'desc');
    }
    
    public function customerStatus(){
        return CustomerStatus::model()->getStatus($this->customer_id);
    }

    public function operator($class = \Common\Models\Staff\Staff::class) {
        return $this->hasOne($class, 'id', 'admin_id')->orderBy('id', 'desc');
    }

    public function isActive() {
        return $this->whereStatus(self::STATUS_NORMAL)->where(function($query) {
                    $query->where('indate', '<', DateHelper::dateTime());
                    $query->orWhere("indate", NULL);
                });
    }

}
