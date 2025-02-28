<?php

namespace Api\Models\Feedback;

/**
 * Api\Models\Feedback\FeedbackFaq
 *
 * @property int $id 反馈ID
 * @property string|null $type 类型
 * @property string $title 标题
 * @property string $content 内容
 * @property int|null $status 状态 1正常 0废弃
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\FeedbackFaq newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\FeedbackFaq newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\FeedbackFaq query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\FeedbackFaq whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\FeedbackFaq whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\FeedbackFaq whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\FeedbackFaq whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\FeedbackFaq whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\FeedbackFaq whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Feedback\FeedbackFaq whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Feedback\FeedbackFaq orderByCustom($column = null, $direction = 'asc')
 */
class FeedbackFaq extends \Common\Models\Feedback\FeedbackFaq
{

    public function safes()
    {
        return [];
    }

    public function getList($param)
    {
        $where = [
            'type' => $param['type'],
            'status' => self::STATUS_NORMAL,
        ];
        $size = array_get($param, 'size');
        return $this->where($where)->paginate($size);
    }

    /**
     * 根据id获取
     * @param $id
     * @return mixed
     */
    public function getOne($id)
    {
        $where = [
            'status' => self::STATUS_NORMAL,
        ];
        return self::whereKey($id)->where($where)->first();
    }

}
