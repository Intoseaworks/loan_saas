<?php

namespace Common\Utils;

use Illuminate\Support\Facades\DB;

/**
 * Trait Helper
 * @package App\Helper
 */
trait Helper
{
    /**
     * @var array
     */
    protected static $staticErrors = [];
    /**
     * @var array
     */
    protected static $helper = [];
    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @param array $params
     * @return static
     */
    public static function helper($params = [])
    {
        $className = static::class;
        if (!isset(static::$helper[$className])) {
            static::$helper[$className] = new $className();
            call_user_func_array([static::$helper[$className], 'init'], $params);
        }

        return self::$helper[$className];
    }

    /**
     * @return array
     */
    public static function getStaticErrors()
    {
        return self::$staticErrors;
    }

    /**
     * @param int $index
     * @param null $default
     * @return mixed|null
     */
    public static function getStaticError($index = -1, $default = null)
    {
        $error = self::$staticErrors[$index == -1 ? count(self::$staticErrors) - 1 : $index] ?? $default;
        self::clearStaticError();
        return $error;
    }

    /**
     * @return array
     */
    public static function clearStaticError()
    {
        return self::$staticErrors = [];
    }

    /**
     * @param $error
     * @return mixed
     */
    protected static function addStaticError($error)
    {
        self::$staticErrors[] = is_array($error) ? json_encode($error, JSON_UNESCAPED_UNICODE) : $error;
        return false;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function clearError()
    {
        return $this->errors = [];
    }

    /**
     * Init
     */
    protected function init()
    {
    }

    /**
     * @param $callback
     * @return mixed
     */
    protected function transaction($callback)
    {
        DB::beginTransaction();
        $result = false;
        try {
            if (!$result = $callback()) {
                throw new \Exception($this->getError() ?: '服务器异常', 500);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            if (!$this->getError() && $e->getMessage()) {
                $this->addError($e->getMessage());
            }
            return $result;
        }
        return $result;
    }

    /**
     * @param int $index
     * @param null $default
     * @return mixed|null
     */
    public function getError($index = -1, $default = null)
    {
        return $this->errors[$index == -1 ? count($this->errors) - 1 : $index] ?? $default;
    }

    /**
     * @param $error
     * @return mixed
     */
    protected function addError($error)
    {
        $this->errors[] = is_array($error) ? json_encode($error, JSON_UNESCAPED_UNICODE) : $error;
        return false;
    }
}