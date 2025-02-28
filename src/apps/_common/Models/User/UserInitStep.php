<?php

namespace Common\Models\User;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\User\UserInitStep
 *
 * @property int $id
 * @property int|null $user_id 用户Id
 * @property string|null $step_name 步骤标识App自定义
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserInitStep newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserInitStep newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserInitStep orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInitStep query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserInitStep whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInitStep whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInitStep whereStepName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInitStep whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInitStep whereUserId($value)
 * @mixin \Eloquent
 */
class UserInitStep extends Model
{

    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'user_init_step';

    protected $fillable = [];

    protected $hidden = [];

    public function textRules()
    {
        return [];
    }

}
