<?php

namespace Tests\Helpers;

use Illuminate\Http\Testing\FileFactory;
use Illuminate\Http\Testing\File;

class FileFactoryExtend  extends FileFactory
{
    public function imageFrom()
    {
        return new File('test-front.jpeg',
            fopen('/var/www/html/baoqy/loan-saas/tests/Api/test-front.jpeg',"wb"));
    }
}
