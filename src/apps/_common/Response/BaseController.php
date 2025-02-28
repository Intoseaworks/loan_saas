<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-18
 * Time: 14:37
 */

namespace Common\Response;

use Common\Traits\Response\Send;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class BaseController extends Controller
{
    use Send;

    /**
     * @var Request|\Laravel\Lumen\Application|mixed
     */
    protected $request;

    protected $rule;

    protected $server;

    protected $params;

    public function __construct()
    {
        $this->request = app(Request::class);
        $this->params = $this->request->all();
    }

    /**
     * 接口参数统一调用方法
     * @param $index
     * @param $default
     * @return mixed
     */
    protected function getParam($index, $default = '')
    {
        return array_get($this->params, $index, $default);
    }

    /**
     * 接口所有参数统一调用方法
     * @return mixed
     */
    protected function getParams()
    {
        $params = [];
        foreach ($this->params as $key => $value) {
            $params[$key] = $value;
        }
        return (array)$params;
    }
}
