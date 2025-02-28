<?php

namespace Common\Utils\UFile;

use Exception;

class UFileException extends Exception
{
    /**
     * @var int
     */
    public $errRet;

    public function __construct($message = "", $code = 0, $ret = 0)
    {
        parent::__construct($message, $code, null);

        $this->errRet = $ret;
    }
}
