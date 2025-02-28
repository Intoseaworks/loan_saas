<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-14
 * Time: 10:21
 */

namespace Common\Exceptions;


use Common\Utils\Code\ApproveStatusCode;
use Exception;
use Throwable;

class CustomException extends Exception
{
    /**
     * CustomException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = ApproveStatusCode::CODE_SHOW_CUSTOMER, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
