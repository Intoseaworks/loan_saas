<?php

namespace Common\Models\NewClm;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\NewClm\ClmRule
 *
 * @property int $id id
 * @property int $type 类型 1:预调整规则 2:限定规则
 * @property string $rule 规则code
 * @property string $rule_name 规则名
 * @property string|null $rule_param 规则参数
 * @property int $rule_param_type 类型 1:字符串 2:范围 3:选项 4:多属性
 * @property string $opt 操作符(操作函数) ADD、MIN 等
 * @property int $value 规则值
 * @property int $status 状态 1:正常 2:禁用
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule whereOpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule whereRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule whereRuleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule whereRuleParam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule whereRuleParamType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule whereValue($value)
 * @mixin \Eloquent
 */
class ClmRule extends Model
{
    use StaticModel;

    protected $table = 'new_clm_rule';
    protected $class = 'default';

    protected $guarded = [];

    // 状态：正常
    const STATUS_NORMAL = 1;
    // 状态：禁用
    const STATUS_DISABLED = 2;
    // 状态
    const STATUS = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_DISABLED => '禁用',
    ];

    // 类型：预调整规则
    const TYPE_ADJUST = 1;
    // 类型：限定规则
    const TYPE_LIMIT = 2;

    // 参数类型：字符串
    const PARAM_TYPE_STRING = 1;
    // 参数类型：范围
    const PARAM_TYPE_RANGE = 2;
    // 参数类型：选项(单个属性，多个选项)
    const PARAM_TYPE_OPTION = 3;
    // 参数类型：多个属性
    const PARAM_TYPE_MULTIPLE = 4;

    // 运算符：+
    const OPT_ADD = 'ADD';

    /*************************************************************************************************************
     * Rule start
     ************************************************************************************************************/

    /** 首次结清未逾期且上次实际借款金额>=其等级可贷额度 */
    const LEVEL_RULE_001 = 'LEVEL_RULE_001';
    /** 首次结清未逾期且上次实际借款金额<其等级可贷额度 */
    const LEVEL_RULE_002 = 'LEVEL_RULE_002';
    /** 首次结清逾期[1,5] */
    const LEVEL_RULE_003 = 'LEVEL_RULE_003';
    /** 首次结清逾期(5,8],本次有卡 */
    const LEVEL_RULE_004 = 'LEVEL_RULE_004';
    /** 首次结清逾期(5,8],本次无卡 */
    const LEVEL_RULE_005 = 'LEVEL_RULE_005';
    /** 首次结清逾期8+ */
    const LEVEL_RULE_006 = 'LEVEL_RULE_006';
    /** 第2-3次结清未逾期且上次实际借款金额>=其等级可贷额度 */
    const LEVEL_RULE_007 = 'LEVEL_RULE_007';
    /** 第2-3次结清未逾期且上次实际借款金额<其等级可贷额度 */
    const LEVEL_RULE_008 = 'LEVEL_RULE_008';
    /** 第2-3次结清逾期[1,5]且上次实际借款金额>=其等级可贷额度 */
    const LEVEL_RULE_009 = 'LEVEL_RULE_009';
    /** 第2-3次结清逾期[1,5]且上次实际借款金额<其等级可贷额度 */
    const LEVEL_RULE_010 = 'LEVEL_RULE_010';
    /** 第2-3次结清逾期(5,8],本品牌历史总逾期次数(包括本次)<2 */
    const LEVEL_RULE_011 = 'LEVEL_RULE_011';
    /** 第2-3次结清逾期（5,8],本品牌历史总逾期次数(包括本次)>=2 */
    const LEVEL_RULE_012 = 'LEVEL_RULE_012';
    /** 第2-3次结清逾期8+ */
    const LEVEL_RULE_013 = 'LEVEL_RULE_013';
    /** 第4次以上结清未逾期且上次实际借款金额>=其等级可贷额度 */
    const LEVEL_RULE_014 = 'LEVEL_RULE_014';
    /** 第4次以上结清未逾期且上次实际借款金额>=其等级可贷额度 */
    const LEVEL_RULE_015 = 'LEVEL_RULE_015';
    /** 逾期(0,5]，本品牌历史最大逾期天数(包括本次)<=1 且上次实际借款金额>=其等级可贷额度 */
    const LEVEL_RULE_016 = 'LEVEL_RULE_016';
    /** 逾期(0,5]，本品牌历史最大逾期天数(包括本次)<=1 且上次实际借款金额<其等级可贷额度 */
    const LEVEL_RULE_017 = 'LEVEL_RULE_017';
    /** 逾期(0,5]，本品牌历史最大逾期天数(包括本次)>1 且上次实际借款金额>=其等级可贷额度 */
    const LEVEL_RULE_018 = 'LEVEL_RULE_018';
    /** 逾期(0,5]，本品牌历史最大逾期天数(包括本次)>1 且上次实际借款金额<其等级可贷额度 */
    const LEVEL_RULE_019 = 'LEVEL_RULE_019';
    /** 逾期(5，8]，本品牌历史最大逾期天数(包括本次)>1 */
    const LEVEL_RULE_020 = 'LEVEL_RULE_020';
    /** 4次以上结清逾期8+ */
    const LEVEL_RULE_021 = 'LEVEL_RULE_021';
    /** 限定规则：无卡限定规则：限定最高等级为4 */
    const LEVEL_RULE_022 = 'LEVEL_RULE_022';
    /** 限定规则：收入限定规则：等级对应的额度<月收入/2 */
    const LEVEL_RULE_023 = 'LEVEL_RULE_023';
    /** 限定规则：性别限定规则：男性最高等级为12 */
    const LEVEL_RULE_024 = 'LEVEL_RULE_024';
    /** 限定规则：增额限定规则：本次调整后的额度不超过上次实际借款额度+1000 */
    const LEVEL_RULE_025 = 'LEVEL_RULE_025';
    /** 限定规则：全平台逾期最大额度限定：如果用户在全平台曾经最大逾期天数>5,本次调整后的额度<全部逾期天数大于等于5天的合同里最小的借款金额 */
    const LEVEL_RULE_026 = 'LEVEL_RULE_026';
    
    const CLASS_DEFAULT = "default";

    /*************************************************************************************************************
     * Rule end
     ************************************************************************************************************/
    
    /**
     * 处理规则参数
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function parseParams()
    {
        switch ($this->rule_param_type) {
            case self::PARAM_TYPE_STRING:
                return [$this->rule_param];
            case self::PARAM_TYPE_RANGE:
            case self::PARAM_TYPE_OPTION:
                return [json_decode($this->rule_param, true)];
            case self::PARAM_TYPE_MULTIPLE:
                return json_decode($this->rule_param, true);
            default:
                throw new \Exception('clm rule type incorrect!');
        }
    }

    /**
     * 获取状态正常的规则-指定类型
     *
     * @param $type
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     * @throws \Exception
     */
    public static function getNormalRule($type, $class='default')
    {
        $rules = self::query()
            ->where('type', $type)
            ->where('class', $class)
            ->where('status', self::STATUS_NORMAL)
            ->get();

        /** @var static $rule */
        foreach ($rules as $rule) {
            $rule->rule_param = $rule->parseParams();
        }

        return $rules;
    }

    /**
     * 获取生效的预调整规则
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     * @throws \Exception
     */
    public static function getAdjustRule($class='default')
    {
        return self::getNormalRule(self::TYPE_ADJUST, $class);
    }

    /**
     * 获取生效的限定规则
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     * @throws \Exception
     */
    public static function getLimitRule($class='default')
    {
        return self::getNormalRule(self::TYPE_LIMIT, $class);
    }
}
