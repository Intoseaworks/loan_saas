<?php
/**
 * Created by PhpStorm.
 * User: Summer
 * Date: 2018/6/13
 * Time: 10:02
 */

namespace Common\Utils\Export\LaravelExcel;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BaseExport implements WithHeadings, WithMapping, WithEvents
{

    use Exportable, RegistersEventListeners;

    protected $headings = [];
    protected $fields = [];
    protected $fileName;
    //格式化column.防止科学计数法
    protected $toString = [];
    /** @var Worksheet $phpspreadsheet */
    protected $phpspreadsheet;
    protected $title;
    protected $user;
    protected $startAt;
    protected $endAt;
    protected $company;
    //columns长度
    protected $columnsLen;
    //处理一些空数据(比如设置0,或者`-`)
    protected $needSetDefaultVauleColumns = [];
    protected $defaultV = '0';
    //第一行写入数据所在的行
    protected $startRowLine = 0;
    //格式化数据
    protected $numberFormatColumns = [];

    protected $_value = [];

    public function __construct($parameters = [])
    {
        //导出Excel时间比较久,设置超时时间
        set_time_limit(0);
        //设置报表参数
        foreach ($parameters as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->_value[$name] ?? null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->_value[$name] = $value;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->headings;
    }

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

        //是否需要设置默认值
        if ($this->needSetDefaultVauleColumns) {
            $this->handleEmptygrid($row);
        }

        $data = [];
        foreach ($this->fields as $field) {
            if (in_array($field, $this->toString)) {
                $data[] = " " . $row->$field;
            } else {
                $data[] = $row->$field;
            }
        }

        return $data;
    }

    protected function handleEmptygrid(&$row)
    {
        foreach ($this->needSetDefaultVauleColumns as $vauleColumn) {
            if (is_object($row)) {
                if (!$row->$vauleColumn) {
                    $row->$vauleColumn = $this->defaultV;
                }
            } else {
                if (!$row[$vauleColumn]) {
                    $row[$vauleColumn] = $this->defaultV;
                }
            }
        }
    }

    /**
     * 生成sheet前触发的事件,可以设置表头一些的东西
     * @param BeforeSheet $event
     */
    public function beforeSheet(BeforeSheet $event)
    {
        $phpspreadsheet = $event->sheet->getDelegate();

        $this->phpspreadsheet = $phpspreadsheet;

        //设置表头
        $this->setTitle();
    }

    /**
     * 设置表头
     * @return $this
     */
    protected function setTitle()
    {
        $endCoordinate = $this->getCellCoordinate();
        $this->phpspreadsheet->mergeCells('A1:' . $endCoordinate . '1')
            ->setCellValue('A1', $this->title)
            ->getStyle('A1')
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        //表头加粗
        $this->phpspreadsheet->getStyle('A1')
            ->getFont()->setBold(true);

        $this->phpspreadsheet->getRowDimension(1)
            ->setRowHeight(31.5);

        $this->phpspreadsheet->mergeCells('A2:' . $endCoordinate . '2')
            ->setCellValue('A2', static::getTitle($this->startAt, $this->endAt, $this->user, $this->company))
            ->getStyle('A2')
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return $this;
    }

    protected function getCellCoordinate($len = 0)
    {
        if ($len == 0) {
            $len = $this->columnsLen;
        }
        $column = range('A', 'Z');
        return $column[$len - 1];
    }

    /**
     * 报表头
     * @param $start_at
     * @param $end_at
     * @param $username
     * @param $company
     * @return string
     */
    public static function getTitle($start_at, $end_at, $username, $company = null)
    {

        if ($start_at) {
            $date = date('Y-m-d', strtotime($start_at)) . '-' . date('Y-m-d', strtotime($end_at));
        } else {
            $date = date('Y-m-d');
        }

        $company = '编制单位: ' . ($company ?: config('report.company'));
        $date = '     日期: ' . $date;
        $username = '     编制人: ' . $username;
        return $company . $date . $username;
    }

    /**
     * sheet生成后触发的事件
     * @param AfterSheet $event
     */
    public function afterSheet(AfterSheet $event)
    {
        $cellCoordinate = 'A1:' . $this->phpspreadsheet->getHighestColumn() . ($this->phpspreadsheet->getHighestRow());
        //设置表格边框风格
        $this->setBorderStyle($cellCoordinate);
        //设置居中
        $this->setAlignCenter($cellCoordinate);
        //设置格式
        $this->numberFormat();
    }

    /**
     * 设置表格边框颜色
     */
    protected function setBorderStyle($cellCoordinate, $styleArray = '')
    {
        if (!$styleArray) {
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => [
                            'rgb' => '000000'
                        ]
                    ],
                ]
            ];
        }

        $this->phpspreadsheet
            ->getStyle($cellCoordinate)
            ->applyFromArray($styleArray);

    }

    /**
     * 设置居中
     * @param $pCellCoordinate
     * @return $this
     */
    protected function setAlignCenter($pCellCoordinate)
    {
        $this->phpspreadsheet->getStyle($pCellCoordinate)
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return $this;
    }

    protected function numberFormat()
    {

        foreach ($this->numberFormatColumns as $numberFormatColumn => $v) {
            $cellCoordinate = "{$numberFormatColumn}1:{$numberFormatColumn}" . $this->phpspreadsheet->getHighestRow();
            $this->phpspreadsheet->getStyle($cellCoordinate)->getNumberFormat()->setFormatCode($v);
        }

        return $this;
    }

    protected function setParameters($columns, $title)
    {
        $this->headings = array_values($columns);

        $this->fields = array_keys($columns);

        $this->title = $title;

        $this->columnsLen = count($this->fields);

        $this->fileName = $this->title . '-' . date("Y-m-d H:i:s", time()) . '.xlsx';

        return $this;
    }

    /**
     * 格式化column
     * @param $pCellCoordinate
     * @param $formatCode 格式
     * @return $this
     */
    protected function formatColumn($pCellCoordinate, $formatCode)
    {
        $this->phpspreadsheet->getStyle($pCellCoordinate)
            ->getNumberFormat()
            ->setFormatCode($formatCode);

        return $this;
    }

    /**
     * 设置宽度
     * @param $pCellCoordinate
     * @return $this
     */
    protected function setWidth($pCellCoordinate, $width)
    {
        $this->phpspreadsheet
            ->getColumnDimension($pCellCoordinate)
            ->setWidth($width);

        return $this;
    }

    /**
     * 插入表格
     * @param array $rows
     * @param int|null $row
     * @param bool $strictNullComparison
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function append(array $rows, string $startCell, int $row = null, bool $strictNullComparison = false)
    {
        if (!$row) {
            $row = 1;
            if ($this->hasRows()) {
                $row = $this->phpspreadsheet->getHighestRow() + 1;
            }
        }

        $this->phpspreadsheet->fromArray($rows, null, $startCell . $row, $strictNullComparison);
    }

    protected function hasRows(): bool
    {
        return $this->phpspreadsheet->cellExists('A1');
    }
}
