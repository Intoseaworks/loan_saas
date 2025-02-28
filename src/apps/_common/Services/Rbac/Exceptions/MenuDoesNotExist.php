<?php

namespace Common\Services\Rbac\Exceptions;

use InvalidArgumentException;

class MenuDoesNotExist extends InvalidArgumentException
{
    /**
     * @param string $menuName
     * @return MenuDoesNotExist
     */
    public static function named(string $menuName)
    {
        return new static("不存在名称为:{$menuName}的菜单");
    }

    /**
     * @param string $path
     * @return MenuDoesNotExist
     */
    public static function withPath(string $path)
    {
        return new static("不存在path为:{$path}的菜单");
    }
}
