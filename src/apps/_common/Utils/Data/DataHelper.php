<?php

namespace Common\Utils\Data;

class DataHelper
{
    /**
     * @return string
     */
    public static function getUniqueId()
    {
        return '18' . round(microtime(true) * 1000) . rand(100, 999);
    }
}
