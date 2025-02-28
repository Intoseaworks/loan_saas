<?php

namespace Admin\Imports\Crm;

use Maatwebsite\Excel\Concerns\ToModel;
use Common\Models\Crm\CrmWhiteList;

class WhiteListImport implements ToModel {

    /**
     * @param array $row
     *
     * @return User|null
     */
    public function model(array $row) {
        return new CrmWhiteList();
    }

}
