<?php

namespace Common\Models\Call;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class CallAdmin extends Model {

    use StaticModel;

    const TYPE_COLLECTION = 1;
    const TYPE_PV = 2;
    const TYPE_TELE = 3;
    const TYPE_TEST = 4;
    const TYPE_APPROVE_AUTO = 5;
    const STATUS_NORMAL = 1; //正常
    const STATUS_FORGET = 2; //失效
    const STATUS = [
        self::STATUS_NORMAL => "正常",
        self::STATUS_FORGET => "禁用",
    ];
    const TYPE = [
        self::TYPE_COLLECTION => "Collection",
        self::TYPE_PV => "Pv",
        self::TYPE_TELE => "Tele",
        self::TYPE_TEST => "Test"
    ];

    /**
     * @var string
     */
    protected $table = 'call_admin';

    public function initExtensionNum($adminId) {
        $call = $this->newQuery()->where('admin_id', $adminId)->get()->first();
        if ($call) {
            return $call->extension_num;
        } else {
            return intval(self::query()->max('extension_num')) + 1;
        }
    }

}
