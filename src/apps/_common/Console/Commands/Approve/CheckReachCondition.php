<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-11-29
 * Time: 21:45
 */

namespace Common\Console\Commands\Approve;


use Approve\Admin\Services\Order\ApproveOrderService;
use Illuminate\Console\Command;

class CheckReachCondition extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approve:check-reach-condition';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '审批单 待补充资料/电二审 检查是否可以审批';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ApproveOrderService::getInstance()->checkReachCondition();
        $this->info('SUCCESS');
    }
}
