<?php

namespace Common\Models\User;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class UserInviteFriend extends Model {

    use StaticModel;

    /** 状态：是否可返现 */
    const STATUS_CASH_BACK_NO = 0;
    const STATUS_CASH_BACK_YES = 1;

    /**
     * @var string
     */
    protected $table = 'user_invite_friend';

    public function invitingUser() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
