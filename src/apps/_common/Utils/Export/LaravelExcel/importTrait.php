<?php
/**
 * Created by PhpStorm.
 * User: Summer
 * Date: 2018/6/13
 * Time: 17:53
 */

namespace Common\Utils\Export\LaravelExcel;

trait importTrait
{
    /**
     * 读取文件
     * @param $inputFileName @文件名称
     * @param bool $delHeader @是否删除表头
     * @return array
     */
    public function importExcel($inputFileName, $delHeader = true)
    {
        /**  Identify the type of $inputFileName  **/
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
        /**  Create a new Reader of the type that has been identified  **/
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        /**  Load $inputFileName to a Spreadsheet Object  **/
        $spreadsheet = $reader->load($inputFileName);

        $datas = $spreadsheet->getActiveSheet()->toArray();
        //删除表头
        if ($delHeader) {
            array_shift($datas);
        }

        return $datas;
    }
}
