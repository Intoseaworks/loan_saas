<?php

namespace Risk\Common\Models\SystemApprove;

/**
 * Risk\Common\Models\SystemApprove\SystemApproveSandboxRecord
 *
 * @property int $id
 * @property int $app_id 商户id
 * @property string|null $module 所属模块 basic:基础 credit:征信
 * @property int $user_id 用户id
 * @property int $order_id 订单id
 * @property int $user_type 用户类型 0:新用户  1:老用户
 * @property int $result 结果 1:通过  2:拒绝
 * @property int $hit_rule_cnt hit_rule命中数
 * @property string|null $hit_rule 命中拒绝规则及对应命中值，json串
 * @property string|null $exe_rule 规则执行记录
 * @property string|null $extra_code 额外标识
 * @property string $description 注释说明
 * @property int|null $relate_id 关联ID
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveSandboxRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveSandboxRecord newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveSandboxRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveSandboxRecord whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveSandboxRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveSandboxRecord whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveSandboxRecord whereExeRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveSandboxRecord whereExtraCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveSandboxRecord whereHitRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveSandboxRecord whereHitRuleCnt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveSandboxRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveSandboxRecord whereModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveSandboxRecord whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveSandboxRecord whereRelateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveSandboxRecord whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveSandboxRecord whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveSandboxRecord whereUserType($value)
 * @mixin \Eloquent
 */
class SystemApproveSandboxRecord extends SystemApproveRecord
{
    /**
     * @var string
     */
    protected $table = 'system_approve_sandbox_record';

    public static function addRecord($params)
    {
        $data = [
            'app_id' => $params['app_id'],
            'module' => $params['module'],
            'user_id' => $params['user_id'],
            'order_id' => $params['order_id'],
            'user_type' => $params['user_type'],
            'result' => $params['result'],
            'hit_rule_cnt' => $params['hit_rule_cnt'],
            'hit_rule' => $params['hit_rule'],
            'exe_rule' => $params['exe_rule'],
            'description' => $params['description'],
            'relate_id' => $params['relate_id'] ?? null,
            'extra_code' => $params['extra_code'] ?? null,
        ];

        return self::create($data);
    }
}
