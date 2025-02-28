<?php

namespace Common\Utils\Export;

use Common\Utils\DingDing\DingHelper;
use Common\Utils\LoginHelper;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class AbstractExport
 * @package Common\Utilst\Export
 * @author ChangHai Zhan
 */
abstract class AbstractExport
{
    /**
     * @var int
     */
    const CHUNK = 3000;

    /**
     * 场景
     *
     * @var string
     */
    protected $sence;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * AbstractExport constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        DingHelper::notice(['class' => static::class, 'admin_id' => LoginHelper::getAdminId()], '导出触发监控', null, false);
        foreach ($params as $key => $param) {
            $this->$key = $param;
        }
    }

    /**
     * @return static
     */
    public static function getInstance()
    {
        $args = func_get_args();
        return new static(...$args);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->params[$name];
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->params[$name] = $value;
    }

    /**
     * @param $provider
     * @param null|string $scene
     * @param bool $warp
     * @param null|string $fileName
     */
    public function export($provider, $scene = null, $warp = true, $fileName = null)
    {
        if ($warp) {
            $provider = $this->warp($provider);
        }

        if ($scene) {
            $this->sence = $scene;
        }

        $this->csvProvider($provider, $this->getColumns($scene), $fileName);
    }

    /**
     * @param Builder $query
     * @return \Closure
     */
    public function warp(Builder $query)
    {
        return function ($num) use ($query) {
            $data = $query->skip(($num - 1) * static::CHUNK)->take(static::CHUNK)->get();
            if ($data->isEmpty()) {
                return false;
            }

            return $data;
        };
    }

    /**
     * @param $provider
     * @param $columns
     * @param null $fileName
     */
    public function csvProvider($provider, $columns, $fileName = null)
    {
        set_time_limit(0);
        //header
        $fileName = date('YmdHis') . '_' . $this->formatGbk($fileName ? $fileName : 'export');
        header('Content-Type: application/vnd.ms-excel;charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $fileName . '.csv"');
        header('Cache-Control: max-age=0');
        if (ob_get_contents()) {
            ob_clean();
        }
        $fp = fopen('php://output', 'a');
        $columns = $this->formatColumns($columns);
        $content = [];
        foreach ($columns as $column) {
            $name = $column['name'] ?? '';
            $content[] = $this->formatGbk($name);
        }
        fputcsv($fp, $content);
        if ($provider instanceof \Closure) {
            $function = $provider;
            $number = 1;
            while ($provider = $function($number)) {
                $number++;
                $this->csvMain($fp, $provider, $columns);
                $this->obFlush();
            }
        } else {
            $this->csvMain($fp, $provider, $columns);
        }
        $this->obFlush();
        fclose($fp);
        die;
    }

    /**
     * @param $str
     * @return string
     */
    protected function formatGbk($str)
    {
        return iconv('utf-8', 'gbk//IGNORE', $str);
    }

    /**
     * @param $columns
     * @return array
     */
    protected function formatColumns($columns)
    {
        $columnsOld = $columns;
        $columns = [];
        foreach ($columnsOld as $value => $name) {
            if (is_array($name)) {
                $columns[] = $name;
            } else {
                $columns[] = [
                    'name' => app('translator')->getLocale() == 'zh-CN' ? $name : t(static::class . '.' . $this->sence . '.' . $name,
                        'export'),
                    'value' => $value,
                    'default' => '',
                ];
            }
        }
        return $columns;
    }

    /**
     * @param $fp
     * @param $provider
     * @param $columns
     */
    protected function csvMain($fp, $provider, $columns)
    {
        $i = 0;
        foreach ($provider as $data) {
            // 对数据进行一些处理
            $this->beforePutCsv($data);
            $content = [];
            foreach ($columns as $column) {
                $value = $column['value'] ?? '';
                $default = $column['default'] ?? null;
                $content[] = "\t" . $this->formatGbk($this->getProviderContent($value, $data, $default));
                $i++;
            }
            fputcsv($fp, $content);
            if ($i == 5000) {
                ob_flush();
                flush();
                $i = 0;
            }
        }
    }

    /**
     * @param $data
     * @return mixed|void
     */
    protected function beforePutCsv($data)
    {
        //
    }

    /**
     * @param $value
     * @param $data
     * @param null $default
     * @return mixed|string
     */
    protected function getProviderContent($value, $data, $default = null)
    {
        $content = '';
        if (is_string($value)) {
            $content = $this->getValue($data, $value, $default);
        } elseif (is_object($value)) {
            $content = $value($data);
        }
        return $content;
    }

    /**
     * @param $data
     * @param $value
     * @param null $default
     * @return mixed
     */
    protected function getValue($data, $value, $default = null)
    {
        $values = [];
        if (strpos($value, '.') !== false) {
            $values = explode('.', $value);
        } else {
            $values[] = $value;
        }

        foreach ($values as $k => $key) {
            if (isset($data->$key)) {
                $data = $data->$key;
            } elseif ($k == 0 && count($values) > 1 && isset($data->{camel_case($key)})) {
                // 兼容关联关系被转换成下划线方式(admin_trade_account|adminTradeAccount)
                $data = $data->{camel_case($key)};
            } elseif (is_array($data) && isset($data[$key])) {
                $data = $data[$key];
            } else {
                $data = $default === null ? $value : $default;
            }
        }

        return $data;
    }

    /**
     * @return void
     */
    public function obFlush()
    {
        if (ob_get_contents()) {
            ob_flush();
            flush();
        }
    }

    /**
     * @param string|null $scene
     * @return array
     */
    abstract public function getColumns($scene = null);
}
