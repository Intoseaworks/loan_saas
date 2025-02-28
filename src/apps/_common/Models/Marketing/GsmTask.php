<?php

namespace Common\Models\Marketing;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class GsmTask extends Model {

    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'marketing_gsm_task';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $hidden = [];

    public function getTelephones($telephones = "") {
        $telephones = $telephones == "" ? $this->telephones : $telephones;
        $telephones = str_replace(["\n", ","], "|", $telephones);
        return explode("|", $telephones);
    }

}
