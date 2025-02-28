<?php

/**
 * Created by PhpStorm.
 * User: Nio
 * Date: 2020/06/01
 * Time: 10:45
 */

namespace Common\Utils\Sms;

class SmsPesoGlobalMktHelper extends SmsPesoGlobalHelper {

    public function getConfig($sendId) {
        $config = json_decode(env('GLOBAL_MKT'), true);
        return $config[$sendId] ?? [];
    }

}
