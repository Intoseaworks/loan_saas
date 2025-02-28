<?php

namespace Common\Services\Rbac\Library\Routing;


use Illuminate\Support\Arr;

class Router extends \Laravel\Lumen\Routing\Router
{
    /**
     *
     * @var array
     */
    public $irregularityRoutes = [];

    public function __construct(\Laravel\Lumen\Application $app)
    {
        parent::__construct($app);
    }

    /**
     * @param $uri
     * @return false|int
     */
    protected function isIrregularUri($uri)
    {
        return (bool)preg_match("#\{.+\}#", $uri);
    }

    /**
     * Merge the given group attributes.
     *
     * @param  array $new
     * @param  array $old
     * @return array
     */
    public function mergeGroup($new, $old)
    {
        $new['namespace'] = static::formatUsesPrefix($new, $old);

        $new['prefix'] = static::formatGroupPrefix($new, $old);

        if (isset($new['domain'])) {
            unset($old['domain']);
        }

        if (isset($old['as'])) {
            $new['as'] = $old['as'] . (isset($new['as']) ? '.' . $new['as'] : '');
        }

        if (isset($old['path'])) {
            $new['path'] = $old['path'] . (isset($new['path']) ? '.' . $new['path'] : '');
        }

        if (isset($old['suffix']) && !isset($new['suffix'])) {
            $new['suffix'] = $old['suffix'];
        }

        return array_merge_recursive(Arr::except($old, ['namespace', 'prefix', 'as', 'path', 'suffix']), $new);
    }

    /**
     * Add a route to the collection.
     *
     * @param  array|string $method
     * @param  string $uri
     * @param  mixed $action
     * @return void
     */
    public function addRoute($method, $uri, $action)
    {
        if ($this->isIrregularUri($uri)) {
            $symbol = str_replace(['{', '}'], '', $uri) . mt_rand(1, 100);
            if (is_string($action)) {
                //字符串
                $action = ['symbol' => $symbol, 'uses' => $action];
            } elseif (is_array($action) && !array_key_exists('symbol', $action)) {
                //数组形式判断是否存在symbol
                $action['symbol'] = $symbol;
            }
        }

        $action = $this->parseAction($action);

        $attributes = null;

        if ($this->hasGroupStack()) {
            $attributes = $this->mergeWithLastGroup([]);
        }

        if (isset($attributes) && is_array($attributes)) {
            if (isset($attributes['prefix'])) {
                $uri = trim($attributes['prefix'], '/') . '/' . trim($uri, '/');
            }

            if (isset($attributes['suffix'])) {
                $uri = trim($uri, '/') . rtrim($attributes['suffix'], '/');
            }

            $action = $this->mergeGroupAttributes($action, $attributes);
        }

        $uri = '/' . trim($uri, '/');

        if (isset($action['as'])) {
            $this->namedRoutes[$action['as']] = $uri;
        }

        if (isset($action['symbol'])) {
            $this->irregularityRoutes[$action['symbol']] = $uri;
        }

        if (is_array($method)) {
            foreach ($method as $verb) {
                $this->routes[$verb . $uri] = ['method' => $verb, 'uri' => $uri, 'action' => $action];
            }
        } else {
            $this->routes[$method . $uri] = ['method' => $method, 'uri' => $uri, 'action' => $action];
        }
    }

    /**
     * Merge the group attributes into the action.
     *
     * @param  array $action
     * @param  array $attributes The group attributes
     * @return array
     */
    protected function mergeGroupAttributes(array $action, array $attributes)
    {
        $namespace = $attributes['namespace'] ?? null;
        $middleware = $attributes['middleware'] ?? null;
        $as = $attributes['as'] ?? null;
        if (isset($attributes['path']) && $attributes['path']) {
            if (isset($action['path'])) {
                $action['path'] .= '.' . $attributes['path'];
            } else {
                $action['path'] = $attributes['path'];
            }
        }
        if (isset($attributes['symbol']) && $attributes['symbol']) {
            $action['symobl'] = $attributes['symbol'];
        }

        return $this->mergeNamespaceGroup(
            $this->mergeMiddlewareGroup(
                $this->mergeAsGroup($action, $as),
                $middleware),
            $namespace
        );
    }

}
