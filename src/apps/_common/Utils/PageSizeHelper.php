<?php

namespace Common\Utils;

use Illuminate\Http\Request;

/**
 * Class PageSizeHelper
 * @package App\Helper
 * @author soliang   @date 2018/4/24
 */
class PageSizeHelper
{
    /**
     *
     * @param int $default
     * @param array $allow
     * @return int|mixed
     * @author soliang   @date 2018/4/24
     */
    public static function getPageSize($default = 15, $allow = [10, 20, 30, 40])
    {
        $request = app(Request::class);
        $pageSize = $request->get('size');
        if (!in_array($pageSize, $allow)) {
            $pageSize = $default;
        }
        return $pageSize;
    }
}
