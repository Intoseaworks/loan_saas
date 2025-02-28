<?php
/**
 * Created by PhpStorm.
 * User: Summer
 * Date: 2018/6/13
 * Time: 10:02
 */

namespace Common\Utils\Export\LaravelExcel;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;

class BaseComplexExport extends BaseExport
{

    //需要统计的字段(计算sum值)
    protected $statisColumns = [];
    protected $statisColumnsLen = 0;
    //需要合并的列
    protected $needMergeColumns = [];
    protected $reverseNeedMergeColumns = [];
    //简化$needMergeColumns
    protected $simpleNeedMergeColumns = [];
    /**
     * 统计合并列
     * @var array
     */
    protected $mergeCells = [];
    //统计和并列
    protected $statisMergeCells = [];
    //数据开始写入行(不计表头)
    protected $startRow;
    //对比列
    protected $preRow1 = [];
    protected $preRow2 = [];
    //字段和统计对应关系
    protected $statisColumnMaps = [];
    //合并列表的第一个字段,用来标识唯一性
    protected $prefix;


    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {

        if (!is_object($row)) {
            $row = (object)$row;
        }

        //处理空表格
        if ($this->needSetDefaultVauleColumns) {
            //这里$row是对象直接传递参数是引用关系
            $this->handleEmptygrid($row);
        }

        //计算合并单元格的位置,这里$row是stdClass对象,是引用传递
        $this->calculateMergeCell($row);

        $data = [];
        //对数据进行筛选
        foreach ($this->fields as $field) {
            if (in_array($field, $this->toString)) {
                $data[] = " " . $row->$field;
            } else {
                $data[] = $row->$field;
            }
        }

        return $data;
    }

    /**
     * 计算合并单元格的高度
     */
    protected function calculateMergeCell($row)
    {
        //遍历需要合并的列,统计合并坐标
        foreach ($this->reverseNeedMergeColumns as $needColumn => $value) {
            //第一次循环
            $compareColumn = $value['mergeCells']['column'];
            $prefix = $this->generateUniquedPrefix($row, $compareColumn);
            if (!array_key_exists($needColumn, $this->preRow1)) {
                $this->preRow1[$needColumn] = $prefix . $row->$compareColumn;
            }

            //对比当前行和上一次行
            if ($this->preRow1[$needColumn] != $prefix . $row->$compareColumn) {
                //记录当前行
                $this->preRow1[$needColumn] = $prefix . $row->$compareColumn;

                //获取当前行数
                $hight = $this->startRow;
                //判断是否需要增加统计列(合并,小计)
                if (!empty($value['statistics'])) {
                    //当前行和上一次不同,给上一次追加统计列(小计,合计)
                    $this->append(
                        array_fill(0, $value['statistics']['mergeCount'], $value['statistics']['value']),
                        $value['statistics']['range'][0]
                    );

                    //excel行数增加
                    $this->startRow++;

                    array_walk($this->statisColumnMaps[$needColumn], function ($v, $k) use ($hight, $needColumn) {
                        //插入统计数据
                        $this->phpspreadsheet->setCellValue($this->statisColumns[$k] . $hight, $v);
                        //数据统计完清除,进行下次统计
                        $this->statisColumnMaps[$needColumn][$k] = 0;
                    });

                    //统计合并(小计,总计)
                    $this->statisMergeCells[$needColumn][] = [
                        //得出合并高度
                        'range' => str_replace(['<a>'], [$hight], $value['statistics']['range']),
                        //合并值
                        'value' => $value['statistics']['value'],
                        'hight' => $hight,
                        'color' => $value['statistics']['color'],
                    ];

                }

                //取出当前column的和并列,得出合并的末尾位置
                //规律:统计所在的行是当前需要合并的列的末尾位置
                $end = array_pop($this->mergeCells[$needColumn]);
                $this->mergeCells[$needColumn][] = [
                    //如果有统计列$hight就是合并列的末尾位置,因为会增加统计列(小计,合计)
                    'range' => str_replace(['<b>'], !empty($value['statistics']) ? $hight : $hight - 1, $end['range']),
                    'value' => $end['value'],
                ];
            }

            //统计放在最后因为,本次循环是这条数据和上一条数据对比.
            //需要先插入统计数据后再计算
            foreach ($this->statisColumns as $statisColumn => $position) {
                $this->statisColumnMaps[$needColumn][$statisColumn] += $row->$statisColumn;
            }
        }

        //统计总计
        foreach ($this->statisColumns as $statisColumn => $position) {
            $this->statisColumnMaps['total'][$statisColumn] += $row->$statisColumn;
        }


        //计算需要合并的列
        foreach ($this->reverseNeedMergeColumns as $needColumn => $value) {

            if (in_array($needColumn, $this->toString)) {
                $row->$needColumn = " " . $row->$needColumn;
            }

            //第一次
            $compareColumn = $value['mergeCells']['column'];
            $prefix = $this->generateUniquedPrefix($row, $compareColumn);
            if (!array_key_exists($needColumn, $this->preRow2)) {
                $this->mergeCells[$needColumn][] = [
                    'range' => str_replace(['<a>'], [$this->startRow], $value['mergeCells']['range']),
                    'value' => $row->$needColumn,
                ];
                $this->preRow2[$needColumn] = $prefix . $row->$compareColumn;
                continue;
            }

            if ($this->preRow2[$needColumn] != $prefix . $row->$compareColumn) {
                $this->preRow2[$needColumn] = $prefix . $row->$compareColumn;
                $this->mergeCells[$needColumn][] = [
                    'range' => str_replace(['<a>'], [$this->startRow], $value['mergeCells']['range']),
                    'value' => $row->$needColumn,
                ];
            }
        }

        //excel行数增加,执行 map() 方法后会添加数据
        $this->startRow++;
    }

    protected function generateUniquedPrefix($row, $column)
    {
        $prefix = '';
        $pos = $this->simpleNeedMergeColumns[$column];
        foreach ($this->needMergeColumns as $needColumn => $value) {
            if ($needColumn == $pos) {
                break;
            }
            $prefix .= $row->{$value['mergeCells']['column']} . '-';
        }
        return $prefix;
    }

    /**
     * 生成表格前事件,做一些添加表头等工作
     * @param BeforeSheet $event
     */
    public function beforeSheet(BeforeSheet $event)
    {

        $this->phpspreadsheet = $event->sheet->getDelegate();

        //设置表头
        $this->setTitle();

    }

    /**
     * 生成sheet后的事件,做一些表格处理(合并,颜色)
     * @param AfterSheet $event
     */
    public function afterSheet(AfterSheet $event)
    {

        //判断数据是否为空
        if ($this->phpspreadsheet->getHighestRow() == $this->startRowLine - 1) {
            return true;
        }

        //最后一次循环后最后一列的小计,合计没有生成,在这里生成
        $this->processEndMerge();

        //添加总统计
        $this->addTotalStatistics();

        //合并列
        $this->handleMerge();

        $cellCoordinate = 'A1:' . $this->phpspreadsheet->getHighestColumn() . ($this->phpspreadsheet->getHighestRow());
        //设置表格边框风格
        $this->setBorderStyle($cellCoordinate);
        //设置居中
        $this->setAlignCenter($cellCoordinate);
        //设置格式
        $this->numberFormat();
    }

    /**
     * 最后一次循环后最后一列的小计,合计没有生成,在这里生成
     */
    protected function processEndMerge()
    {

        foreach ($this->reverseNeedMergeColumns as $needColumn => $value) {

            $hight = $this->startRow;

            //判断是否需要增加统计列(合并,小计)
            if (!empty($value['statistics'])) {
                //增加一行统计列
                $this->append(
                    array_fill(0, $value['statistics']['mergeCount'], $value['statistics']['value']),
                    $value['statistics']['range'][0]
                );

                //excel行数增加,因为上面append了数据
                $this->startRow++;

                //配置统计列的相关参数
                $this->statisMergeCells[$needColumn][] = [
                    'range' => str_replace(['<a>'], [$hight], $value['statistics']['range']),
                    'value' => $value['statistics']['value'],
                    'hight' => $hight,
                    'color' => $value['statistics']['color'],
                ];

                //处理最后一次统计
                array_walk($this->statisColumnMaps[$needColumn], function ($v, $k) use ($hight, $needColumn) {
                    $this->phpspreadsheet->setCellValue($this->statisColumns[$k] . $hight, $v);
                });

            }

            //配置上一次需要合并的末尾位置
            $end = array_pop($this->mergeCells[$needColumn]);
            $this->mergeCells[$needColumn][] = [
                'range' => str_replace(['<b>'], !empty($value['statistics']) ? $hight : $hight - 1, $end['range']),
                'value' => $end['value'],
            ];
        }
    }

    protected function addTotalStatistics()
    {
        //总统计
        $len = $this->columnsLen - $this->statisColumnsLen;
        $this->append(
            array_fill(0, $len, '总计'),
            'A'
        );

        $endColumn = $this->getCellCoordinate($len);
        $this->statisMergeCells[$this->prefix][] = [
            'range' => "A{$this->startRow}:{$endColumn}{$this->startRow}",
            'value' => '总计',
            'hight' => $this->startRow,
            'color' => 'd9d9d9',
        ];

        //统计总数据
        $position = $this->startRow;
        array_walk($this->statisColumnMaps['total'], function ($v, $k) use ($position) {
            $this->phpspreadsheet->setCellValue($this->statisColumns[$k] . $position, $v);
        });

    }

    /**
     * 处理合并列
     */
    protected function handleMerge()
    {
        //处理需要合并的列
        foreach ($this->mergeCells as $mergeCell) {
            //合并
            foreach ($mergeCell as $item) {
                $this->phpspreadsheet
                    ->mergeCells($item['range'])
                    ->setCellValue(explode(':', $item['range'])[0], $item['value']);
            }
        }

        //处理统计合并(小计,合计)
        foreach ($this->statisMergeCells as $column => $mergeCell) {

            foreach ($mergeCell as $key => $item) {
                //合并
                $this->phpspreadsheet
                    ->mergeCells($item['range'])
                    ->setCellValue(explode(':', $item['range'])[0], $item['value']);

                //添加颜色
                $this->phpspreadsheet
                    ->getStyle(explode(':', $item['range'])[0])
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB($item['color']);
            }
        }
    }

    protected function setParameters($columns, $title)
    {
        $this->headings = array_values($columns);

        $this->fields = array_keys($columns);

        $this->title = $title;

        $this->columnsLen = count($this->fields);

        $this->fileName = $this->title . '-' . date("Y-m-d H:i:s", time()) . '.xlsx';

        $this->reverseNeedMergeColumns = array_reverse($this->needMergeColumns);

        $this->prefix = $this->needMergeColumns ? array_keys($this->needMergeColumns)[0] : 'empty';

        //初始化需要统计的列
        $statisTotal = array_merge(array_keys($this->needMergeColumns), ['total']);
        foreach ($statisTotal as $value) {
            array_walk($this->statisColumns, function ($v, $k) use ($value) {
                $this->statisColumnMaps[$value][$k] = 0;
            });
        }

        foreach ($this->needMergeColumns as $needMergeColumn => $value) {
            $this->simpleNeedMergeColumns[$value['mergeCells']['column']] = $needMergeColumn;
        }

        //记录第一行数据写入的行
        $this->startRowLine = $this->startRow;

        $this->statisColumnsLen = count($this->statisColumns);

        return $this;
    }

}
