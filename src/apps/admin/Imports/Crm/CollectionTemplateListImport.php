<?php

namespace Admin\Imports\Crm;

use Common\Models\Crm\CollectionSmsTemplate;
use Maatwebsite\Excel\Concerns\ToModel;

class CollectionTemplateListImport implements ToModel {

    /**
     * @param array $row
     *
     * @return User|null
     */
    public function model(array $row) {
        return new CollectionSmsTemplate();
    }

}
