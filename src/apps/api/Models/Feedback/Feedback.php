<?php

namespace Api\Models\Feedback;

use Common\Utils\MerchantHelper;

/**
 * Api\Models\Feedback\Feedback
 *
 * @property int $id 反馈ID
 * @property int|null $user_id 用户ID
 * @property string|null $type 类型
 * @property string $content 内容
 * @property string|null $telephone 联系电话
 * @property int|null $status 状态 1正常 0废弃
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\Feedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\Feedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\Feedback query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\Feedback whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\Feedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\Feedback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\Feedback whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\Feedback whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\Feedback whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\Feedback whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\Feedback whereUserId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\Feedback orderByCustom($column = null, $direction = 'asc')
 * @property int $app_id feedback
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\Feedback whereAppId($value)
 */
class Feedback extends \Common\Models\Feedback\Feedback
{

    const SCENARIO_CREATE = 'create';

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'app_id' => MerchantHelper::getAppId(),
                'user_id',
                'type',
                'content',
                'telephone',
                'pic_1',
                'pic_2',
                'pic_3',
                'contact_info',
                'status' => self::STATUS_NOMAL,
            ],
        ];
    }

    /**
     * 显示场景过滤
     * @return array
     */
    public function texts()
    {
        return [
        ];
    }

    /**
     * 显示非过滤
     * @return array
     */
    public function unTexts()
    {
        return [
        ];
    }

    /**
     * 显示规则
     * @return array
     */
    public function textRules()
    {
        return [
        ];
    }

    public function add($data)
    {
        return $this->setScenario(self::SCENARIO_CREATE)->saveModel($data);
    }

}
