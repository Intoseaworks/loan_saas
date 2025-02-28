<?php
/**
 * Created by PhpStorm.
 * User: Sun
 * Date: 2019/11/15
 * Time: 14:03
 * author: soliang
 */

namespace Tests\UnitTest\Perfios;

use Common\Utils\UFile\Lib\MimeType;
use Common\Utils\UFile\Lib\Util;
use Common\Utils\UFile\UFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UFileTest extends TestCase
{
    public function testUFileBase()
    {
        $proxySuffix = '.ind-mumbai.ufileos.com';
        $bucket = 'india-ox';
        $publicKey = 'TOKEN_53259735-168d-4d37-bb02-248f05f50b25';
        $privateKey = '026fbb13-4728-4766-9952-72ff5a82cf52';

        $uFile = new UFile($proxySuffix, $bucket, $publicKey, $privateKey);

        $key = 'data/saas/dev/test/test.jpg';
        $file = __DIR__ . '/tmp/test.jpg';
        //$key1 = 'data/saas/dev/test/testpdf.pdf';
        //$file1 = storage_path('app/testpdf.pdf');

        // 清理旧文件
        $uFile->delete($key);
        // exists
        $res = $uFile->exists($key);
        $this->assertEquals($res, false, 'exists or delete');

        // put 直接上传文件内容
        $content = Util::getFileContent($file);
        $mime = MimeType::getFileMimeType($file);
        $res = $uFile->put($key, $content, $mime);
        $resE = $uFile->exists($key);
        $url = $uFile->generatePrivateUrl($key);
        $this->assertEquals($res && $resE, true, 'put');
        $this->line($url, 'put result url');

        // size
        $size = filesize($file);
        $uSize = $uFile->size($key);
        $this->assertEquals($size, $uSize, 'size');

        // putFile + 分片上传
        $key = 'data/saas/dev/test/test1.jpg';
        $uFile->delete($key);
        $res = $uFile->uploadFile($key, $file);
        $resE = $uFile->exists($key);
        $url = $uFile->generatePrivateUrl($key);
        $this->assertEquals($res && $resE, true, 'uploadFile');
        $this->line($url, 'uploadFile');

        // 分片上传
        $key = 'data/saas/dev/test/test2.jpg';
        $uFile->delete($key);
        $uFile->minitUpload($key, $file);
        $resE = $uFile->exists($key);
        $url = $uFile->generatePrivateUrl($key);
        $this->assertEquals($res && $resE, true, 'minitUpload');
        $this->line($url, 'minitUpload');

        // getFile 下载文件
        $localPath = __DIR__ . '/tmp/testTmp.jpg';
        @unlink($localPath);
        $ex = $uFile->exists($key);
        $res = false;
        if ($ex) {
            $res = $uFile->getFile($key, $localPath);
        }
        $ex = file_exists($localPath);
        unlink($localPath);
        $this->assertEquals($res && $ex, true, 'getFile');

        // delete
        $res = $uFile->delete($key);
        $this->assertEquals($res, true, 'delete');
    }

    public function testStorage()
    {
        $storage = Storage::disk('ufile');

        $key = 'data/saas/dev/test/test.jpg';
        $file = __DIR__ . '/tmp/test.jpg';

        $storage->delete($key);

        // exists
        $res = $storage->exists($key);
        $this->assertEquals($res, false, 'exists or delete');

        // putAs
        $res = $storage->putAs($key, $file);
        $resE = $storage->exists($key);
        $url = $storage->ossUrl($key);
        $this->assertEquals($res && $resE, true, 'putAs');
        $this->line($url, 'putAs');

        // size
        $size = filesize($file);
        $uSize = $storage->size($key);
        $this->assertEquals($size, $uSize, 'size');

        // putRemoteFile
        $key0 = 'data/saas/dev/test/test1.jpg';
        $storage->delete($key0);
        $url = $storage->ossUrl($key);
        $res = Storage::disk('ufile')->putRemoteFile($key0, $url);
        $resE = $storage->exists($key0);
        $url = $storage->ossUrl($key0);
        $this->assertEquals($res && $resE, true, 'putAs');
        $this->line($url, 'putRemoteFile');

        // getFile 下载文件
        $localPath = __DIR__ . '/tmp/testTmp.jpg';
        @unlink($localPath);
        $ex = $storage->exists($key);
        $res = false;
        if ($ex) {
            $res = $storage->getFile($key, $localPath);
        }
        $ex = file_exists($localPath);
        unlink($localPath);
        $this->assertEquals($res && $ex, true, 'getFile');

        // delete
        $res = $storage->delete($key);
        $this->assertEquals($res, true, 'delete');
    }

    protected function line($str, $msg = '')
    {
        echo "———————————————————— {$msg} ————————————————————\n\r" . $str;
        echo "\n\r";
    }
}
