<?php

namespace Common\Services\Rbac\Exceptions;

use InvalidArgumentException;

class OperationDoesNotExist extends InvalidArgumentException
{
    /**
     * @param string $operationName
     * @return OperationDoesNotExist
     */
    public static function named(string $operationName)
    {
        return new static("不存在名称为:{$operationName}的操作");
    }

    /**
     * @param string $id
     * @return OperationDoesNotExist
     */
    public static function withId(string $id)
    {
        return new static("不存在Id为:{$id}的操作");
    }
}
