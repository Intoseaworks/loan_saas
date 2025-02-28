<?php

namespace Common\Models\Marketing;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class GsmSender extends Model {

    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'marketing_gsm_sender';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $hidden = [];

    public function getRand() {
        $res = $this->newModelQuery()->where("status", 1)->get()->toArray();
        if ($res) {
            return $res[array_rand($res)] ?? [];
        }
        return [];
    }

}
