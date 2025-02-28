<?php
/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/1/29
 * Time: 16:08
 */

namespace Admin\Models\Feedback;

use Common\Models\User\User;

/**
 * Admin\Models\Feedback\Feedback
 *
 * @property int $id 反馈ID
 * @property int|null $user_id 用户ID
 * @property string|null $type 类型
 * @property string $content 内容
 * @property string|null $telephone 联系电话
 * @property int|null $status 状态 1正常 0废弃
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property-read \Common\Models\User\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Feedback\Feedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Feedback\Feedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Feedback\Feedback query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Feedback\Feedback whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Feedback\Feedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Feedback\Feedback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Feedback\Feedback whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Feedback\Feedback whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Feedback\Feedback whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Feedback\Feedback whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Feedback\Feedback whereUserId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\Feedback orderByCustom($column = null, $direction = 'asc')
 * @property int $app_id feedback
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Feedback\Feedback whereAppId($value)
 */
class Feedback extends \Common\Models\Feedback\Feedback
{
    const SCENARIO_LIST = 'list';

    public function safes()
    {
        return [];
    }

    public function texts()
    {
        return [
            self::SCENARIO_LIST => [
                'id',
                'type',
                'email',
                'telephone',
                'content',
                'created_at'
            ],
        ];
    }

    public function textRules()
    {
        return [
            'array' => [
                'type' => self::TYPE,
            ],
        ];
    }

    /**
     * @param $param
     * @return Feedback|\Illuminate\Database\Eloquent\Builder
     */
    public function search($param)
    {
        $query = $this->newQuery();
        $query->select(['feedback.*']);
        $query->with(['user']);

        //借款时间
        $time_start = array_get($param, 'time_start');
        $time_end = array_get($param, 'time_end');

        if ($time_start && $time_end) {
            $query->whereBetween('feedback.created_at', [$time_start, $time_end]);
        }

        //关键词
        $keyword = array_get($param, 'keyword');
        if (isset($keyword)) {
            $query->whereHas('user', function ($user) use ($keyword) {
                $keyword = '%' . trim($keyword) . '%';
                $user->where('fullname', 'like', $keyword);
                $user->orWhere('telephone', 'like', $keyword);
            });
        }
        return $query->orderBy('feedback.id', 'desc');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
