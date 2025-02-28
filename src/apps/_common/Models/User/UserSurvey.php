<?php

namespace Common\Models\User;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\User\UserSurvey
 *
 * @property int $id
 * @property string|null $channel_name 用途频道名
 * @property int|null $user_id 用户
 * @property string|null $title 标题
 * @property string|null $content 提交内容
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|UserSurvey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSurvey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSurvey orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSurvey query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSurvey whereChannelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSurvey whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSurvey whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSurvey whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSurvey whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSurvey whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSurvey whereUserId($value)
 * @mixin \Eloquent
 */
class UserSurvey extends Model
{
    use StaticModel;
    const SCENARIO_CREATE = 'create';
    const CHANNEL_DETENTION = 'Detention';
    const CHANNELS = [
        self::CHANNEL_DETENTION
    ];

    protected $table = 'user_survey';
    protected $fillable = [];
    protected $hidden = [];

    public function textRules()
    {
        return [];
    }

    public function safes()
    {
        return [];
    }
}
