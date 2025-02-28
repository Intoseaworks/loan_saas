<?php

namespace Common\Models\Inbox;

use Common\Models\User\User;
use Common\Traits\Model\StaticModel;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Inbox\Inbox
 *
 * @property int $id 私信id
 * @property int $user_id 用户id
 * @property string $title 标题
 * @property string $content 内容
 * @property string $created_at 创建时间
 * @property string $read_time 读取时间
 * @property int $status 状态（未读、已读）
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Inbox\Inbox newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Inbox\Inbox newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Inbox\Inbox query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Inbox\Inbox whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Inbox\Inbox whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Inbox\Inbox whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Inbox\Inbox whereReadTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Inbox\Inbox whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Inbox\Inbox whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Inbox\Inbox whereUserId($value)
 * @mixin \Eloquent
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Inbox\Inbox whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Inbox\Inbox orderByCustom($column = null, $direction = 'asc')
 * @property int $app_id app_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Inbox\Inbox whereAppId($value)
 */
class Inbox extends Model
{
    use StaticModel;

    const INBOX_STATUS_UNREAD = 0;
    const INBOX_STATUS_READ = 1;
    const INBOX_STATUS_DELETE = -1;

    const SCENE_CREATE = 'create';

    /**
     * @var string
     */
    public $table = 'inbox';

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
            self::SCENE_CREATE => [
                'user_id',
                'app_id',
                'title',
                'content',
                'status' => self::INBOX_STATUS_UNREAD,
            ]
        ];
    }

    public function sortCustom()
    {
        return [
            'created_at' => [
                'field' => 'created_at',
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

    public function create($userId, $title, $content)
    {
        $user = (new User)->getOne($userId);
        if(!$user){
            DingHelper::notice([
                'userId' => $userId,
                'appId' => MerchantHelper::getAppId(),
                'merchantId' => MerchantHelper::getMerchantId(),
                'title' => $title,
                'content' => $content,
            ], '异常，Inbox用户不存在');
            return false;
        }
        $data = [
            'app_id' => $user->app_id,
            'user_id' => $userId,
            'title' => $title,
            'content' => $content,
        ];
        return Inbox::model(self::SCENE_CREATE)->saveModel($data);
    }

    public function user($class = User::class)
    {
        return $this->hasOne($class, 'id', 'user_id');
    }

    /**
     * 获取未读消息数
     *
     * @param $userId
     * @return int
     */
    public static function getUnreadSum($userId)
    {
        return static::where(['user_id' => $userId, 'status' => static::INBOX_STATUS_UNREAD])->count();
    }
}
