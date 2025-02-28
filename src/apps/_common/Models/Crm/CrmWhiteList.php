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
use Illuminate\Database\Eloquent\Model;
use Common\Utils\Data\DateHelper;
use Common\Models\Crm\CustomerStatus;

/**
 * Common\Models\Crm\CrmWhiteList
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
 * @property int|null $customer_id customer_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList whereIdNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList whereIdType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList whereIndate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteList whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CrmWhiteList extends Model {

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
    
    const MATCHING_RULE = [
        "1" => "手机号码精确匹配",
        "2" => "手机号码&姓名精确匹配",
        "3" => "姓名&出生日期精确匹配",
        "4" => "证件号码&姓名精确匹配",
        "5" => "证件号码精确匹配",
        "6" => "邮箱精确匹配",
    ];
    
    const GREY_LIST_RULE = [
        "1" => "不导入白名单",
        "2" => "导入并移出灰名单",
        "3" => "导入不移出灰名单",
    ];
    
    const STATUS = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_FORGET => '失效'
    ];

    protected $table = 'crm_white_list';

    public function crmWhiteBatch($class = CrmWhiteBatch::class) {
        return $this->hasOne($class, 'id', 'batch_id')->orderBy('id', 'desc');
    }
    
    public function operator($class = \Common\Models\Staff\Staff::class){
        return $this->hasOne($class, 'id', 'admin_id')->orderBy('id', 'desc');
    }
    
    public function customer($class = Customer::class) {
        return $this->hasOne($class, 'id', 'customer_id')->orderBy('id', 'desc');
    }
    
    public function isActive(){
        return $this->whereStatus(self::STATUS_NORMAL)->where(function($query) {
            $query->where('indate', '>', DateHelper::dateTime());
            $query->orWhere("indate", NULL);
        });
    }
    
    public function customerStatus(){
        return CustomerStatus::model()->getStatus($this->customer_id);
    }
    
}
