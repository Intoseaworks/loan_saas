<?php

namespace Common\Middleware;

use Closure;
use Common\Utils\Export\LaravelExcel\ResponseFactory;

class ExcelMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        class_alias('Maatwebsite\Excel\Facades\Excel', 'Excel');

        $app = app();

        $app->register(\Maatwebsite\Excel\ExcelServiceProvider::class);

        $app->singleton('Illuminate\Contracts\Routing\ResponseFactory', function ($app) {
            return new ResponseFactory;
        });

        return $next($request);
    }
}
