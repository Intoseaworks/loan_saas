<?php

namespace Api\Models\Plugin;

use Common\Utils\MerchantHelper;

/**
 * Api\Models\Upload\Upload
 *
 * @property int $id ID
 * @property int $user_id 用户ID
 * @property int $type 用途类型
 * @property int $status 状态-1:已删除 1:正常
 * @property int $source_id 来源ID
 * @property string $filename 文件名称
 * @property string $path 文件地址
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @property string $ext_info 扩展信息
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Upload\Upload newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Upload\Upload newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Upload\Upload query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Upload\Upload whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Upload\Upload whereExtInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Upload\Upload whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Upload\Upload whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Upload\Upload wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Upload\Upload whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Upload\Upload whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Upload\Upload whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Upload\Upload whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Upload\Upload whereUserId($value)
 * @mixin \Eloquent
 * @property int $user_type 来源类型 1:APP 2:管理后台
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Upload\Upload whereUserType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Upload\Upload orderByCustom($column = null, $direction = 'asc')
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Upload\Upload whereMerchantId($value)
 * @property int $app_id app_id
 * @property string|null $apply_id
 * @property string|null $email
 * @property string|null $first_name
 * @property string|null $data_id
 * @property string|null $last_name
 * @property string|null $name
 * @property string|null $name_format
 * @property string|null $short_name
 * @property string|null $picture
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook whereApplyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook whereDataId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook whereNameFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook wherePicture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook whereShortName($value)
 */
class Facebook extends \Common\Models\Plugin\Facebook
{
    const SCENARIO_CREATE = 'create';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'app_id' => MerchantHelper::getAppId(),
                'user_id',
                'apply_id',
                'type',
                'email',
                'first_name',
                'data_id',
                'last_name',
                'name',
                'name_format',
                'short_name',
                'picture',
                'created_at',
                'updated_at'
            ],
        ];
    }

    public function getDataByUserId($userId)
    {
        return $this->where(['user_id' => $userId])->count();
    }

}
