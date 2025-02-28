<?php

namespace Common\Models\Action;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Action\ActionName
 *
 * @property int $id
 * @property string $name 动作名称[英文]
 * @property string $nickname 动作别名[中文]
 * @property int $status 状态 0禁用 1启用
 * @property string $stat_type 统计方式 pv统计 uv统计 uid统计
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ActionName newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActionName newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActionName orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ActionName query()
 * @method static \Illuminate\Database\Eloquent\Builder|ActionName whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActionName whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActionName whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActionName whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActionName whereStatType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActionName whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActionName whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ActionName extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    public $table = 'action_name';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $dates = [];

    protected static function boot()
    {
        parent::boot();

        static::setAppIdBootScope();
    }

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [

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

}
