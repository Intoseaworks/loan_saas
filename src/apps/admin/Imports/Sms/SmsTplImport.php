<?php

namespace Admin\Imports\Sms;

use Maatwebsite\Excel\Concerns\ToModel;
use Common\Models\Sms\SmsTpl;

class SmsTplImport implements ToModel {

    /**
     * @param array $row
     *
     * @return User|null
     */
    public function model(array $row) {
        return new SmsTpl();
    }

}
