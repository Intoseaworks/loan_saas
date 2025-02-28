<?php

namespace Common\Services\Rbac\Exceptions;

use InvalidArgumentException;

class OperationAlreadyExists extends InvalidArgumentException
{
    public static function create()
    {
        return new static('当前菜单下功能已经存在');
    }
}
