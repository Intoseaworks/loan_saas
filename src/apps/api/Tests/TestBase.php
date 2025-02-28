<?php

namespace Api\Tests;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TestBase extends TestCase
{
    /**
     * 获取旧 cashnow dev 环境数据库链接
     * @param $table
     * @return \Illuminate\Database\Connection|\Illuminate\Database\ConnectionInterface|\Illuminate\Database\Query\Builder
     */
    protected function getOldDbConnect($table = null)
    {
        $connect = DB::connection('cash-now');

        if ($table) {
            return $connect->table($table);
        }

        return $connect;
    }
}
