<?php

namespace Common\Models\Message;

use Common\Models\Staff\Staff;
use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Common\Models\Message\MessageUser
 *
 * @property int $id
 * @property int $message_id
 * @property int $user_id
 * @property int $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $send_time
 * @method static \Illuminate\Database\Eloquent\Builder|MessageUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageUser orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageUser whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageUser whereSendTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageUser whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageUser whereUserId($value)
 * @mixin \Eloquent
 */
class MessageUser extends Model
{
    use StaticModel;

    protected $table = 'message_user';

    protected $guarded = [];

    /** 状态：已创建 */
    const STATUS_CREATE = 0;
    /** 状态：已发送 */
    const STATUS_SEND = 1;
    /** 状态：已读 */
    const STATUS_READ = 2;
    const STATUS = [
        self::STATUS_SEND => '已发送',
        self::STATUS_READ => '已读',
    ];

    /**
     * @param $template
     * @param $merchantId
     * @param $templateParams
     * @return mixed|Message
     * @throws \Exception
     */
    public static function sendByTemplateToMerchant($template, $merchantId, $templateParams)
    {
        DB::beginTransaction();
        try {
            $message = Message::add($merchantId, $template, $templateParams);

            $userId = $message->merchant->staff->pluck('id')->toArray();

            $res = MessageUser::setMessageItem($message, $userId);

            if (!$res) {
                throw new \Exception('message_user 插入失败');
            }

            DB::commit();
            return $message;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function setMessageItem(Message $message, $userIds)
    {
        $userIds = (array)$userIds;
        $insertData = [];
        foreach ($userIds as $userId) {
            $insertData[] = [
                'message_id' => $message->id,
                'user_id' => $userId,
                'status' => self::STATUS_CREATE,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        return self::insert($insertData);
    }

    public static function updateSendByMessageId($messageId)
    {
        $update = [
            'status' => self::STATUS_SEND,
            'send_time' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $where = [
            'message_id' => $messageId,
            'status' => self::STATUS_CREATE,
        ];

        return self::where($where)->update($update);
    }

    public static function getMessageByUser($staffId, $where = [])
    {
        $insideWhere = [
            'status' => self::STATUS_SEND,
            'user_id' => $staffId,
        ];

        return static::query()->with('message')
            ->whereHas('message')
            ->where($insideWhere)
            ->where($where)
            ->orderByDesc('send_time')
            ->get();
    }

    public static function updateStatusToRead($userId, $id)
    {
        $id = (array)$id;

        $update = [
            'status' => self::STATUS_READ,
            'user_id' => $userId,
        ];

        return self::query()->whereIn('id', $id)->update($update);
    }

    /**
     * 关联 message 表
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function message($class = Message::class)
    {
        return $this->belongsTo($class, 'message_id', 'id');
    }

    /**
     * 关联 staff 表
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user($class = Staff::class)
    {
        return $this->belongsTo($class, 'user_id', 'id');
    }
}
