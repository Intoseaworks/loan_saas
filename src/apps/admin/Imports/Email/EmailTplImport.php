<?php

namespace Admin\Imports\Email;

use Maatwebsite\Excel\Concerns\ToModel;
use Common\Models\Email\EmailTpl;

class EmailTplImport implements ToModel {

    /**
     * @param array $row
     *
     * @return User|null
     */
    public function model(array $row) {
        return new EmailTpl();
    }

}
