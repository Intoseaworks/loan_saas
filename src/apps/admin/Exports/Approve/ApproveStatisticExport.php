<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-02-27
 * Time: 17:32
 */

namespace Admin\Exports\Approve;

use Admin\Models\Order\Order;
use Admin\Models\Staff\Staff;
use Admin\Services\Approve\ApproveServer;
use Common\Utils\Data\StringHelper;
use Common\Utils\Export\AbstractExport;

class ApproveStatisticExport extends AbstractExport
{
    /**
     * 审批效率
     */
    const SCENE_STATISTIC_LIST = 'SCENE_STATISTIC_LIST';

    /**
     * @var ApproveServer
     */
    protected $server;

    /**
     * ApproveExport constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->server = ApproveServer::server();
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns($scene = null)
    {
        $columns = [
            static::SCENE_STATISTIC_LIST => [
                'username' => 'Approver',
                'type_text' => 'Approval type',
                'total' => 'Total approval',
                'pass' => 'Passed approval',
                'return' => 'Retured approval',
                'reject' => 'Rejected approval',
                'missed' => 'Missed approval',
                'pass_rate' => 'Pass rate',
                'cost' => 'Man-haur',
            ],
        ];

        return $columns[$scene] ?? [];
    }

    /**
     * @param $order
     * @return mixed|void
     */
    protected function beforePutCsv($order)
    {

    }
}
