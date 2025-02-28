<?php

namespace Admin\Imports\Crm;


use Common\Models\Coupon\CouponTaskCustomImport;
use Maatwebsite\Excel\Concerns\ToModel;

class CouponTaskCustomListImport implements ToModel {

    /**
     * @param array $row
     *
     * @return User|null
     */
    public function model(array $row) {
        return new CouponTaskCustomImport();
    }

}
