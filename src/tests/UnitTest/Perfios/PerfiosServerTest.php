<?php
/**
 * Created by PhpStorm.
 * User: Sun
 * Date: 2019/11/15
 * Time: 14:03
 * author: soliang
 */

namespace Tests\UnitTest\Perfios;

use Common\Services\Perfios\PerfiosServer;
use Tests\TestCase;

class PerfiosServerTest extends TestCase
{
    // test
    // https://demo.perfios.com

    // prod
    // https://www.perfios.com


    public function testStartProcess()
    {
        $userId = '2';
        $type = '1';
        $returnUrl = '';

        $result = PerfiosServer::server()->startProcess($userId, $type, $returnUrl);
        dd($result);
    }
}
