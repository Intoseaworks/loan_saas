<?php
/**
 * Created by PhpStorm.
 * User: Summer
 * Date: 2018/6/13
 * Time: 10:02
 */

namespace Approve\Admin\Reports;

use Common\Utils\Export\LaravelExcel\BaseExport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ApproveStatisticExport extends BaseExport implements FromCollection
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @return mixed
     */
    public function export()
    {
        return $this->init()->download($this->fileName);
    }

    /**
     * @return ApproveStatisticExport
     */
    public function init()
    {
        $columns = [
            'username' => 'Approver',
            'type_text' => 'Approval type',
            'total' => 'Total approval',
            'pass' => 'Passed approval',
            'return' => 'Retured approval',
            'reject' => 'Rejected approval',
            'missed' => 'Missed approval',
            'pass_rate' => 'Pass rate',
            'cost' => 'Man-hour',
            'summary' => 'Approval A.T.',
        ];

        $this->needSetDefaultVauleColumns = ['total', 'pass', 'return', 'reject', 'missed'];

        return $this->setParameters($columns, 'Approval statistics');
    }

    /**
     * @param BeforeSheet $event
     */
    public function beforeSheet(BeforeSheet $event)
    {
        parent::beforeSheet($event);
    }

    /**
     * @param AfterSheet $event
     */
    public function afterSheet(AfterSheet $event)
    {
        parent::afterSheet($event);
    }

    /**
     * @return $this|BaseExport
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function setTitle()
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
            ->setCellValue('A2', $this->getSummary())
            ->getStyle('A2')
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return $this;
    }

    /**
     * @return string
     */
    protected function getSummary()
    {
        if ($this->approveTimeStart || $this->approveTimeEnd) {
            $approveTime = "{$this->approveTimeStart} to {$this->approveTimeEnd}";
        } else {
            $approveTime = "all";
        }
        return "Approval time:{$approveTime}                     Approval type:{$this->aprpoveType}                     Approver:{$this->approver}";
    }

    /**
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        $this->collection = collect($data);
        return $this;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->collection;
    }
}
