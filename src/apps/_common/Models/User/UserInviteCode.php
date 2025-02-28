<?php

namespace Common\Models\User;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class UserInviteCode extends Model {

    use StaticModel;

    /** 状态：正常 */
    const STATUS_ACTIVE = 0;

    /**
     * @var string
     */
    protected $table = 'user_invite_code';

    public function getCodeByUser($userId) {
        $where = [
            'user_id' => $userId,
//            'invited_user' => self::STATUS_ACTIVE
        ];
        return $this->where($where)->first();
    }

    public function getUserByCode($code) {
        $where = [
            'invite_code' => $code,
//            'invited_user' => self::STATUS_ACTIVE
        ];
        return $this->where($where)->first();
    }

    public function getInvitedUser($userId) {
        $where = [
            'user_id' => $userId
        ];
        return $this->with('invitedUser')->has('invitedUser')->where($where)->orderByDesc('updated_at')->get();
    }

    public function invitedUser() {
        return $this->belongsTo(User::class, 'invited_user');
    }

    public function invitingUser() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
