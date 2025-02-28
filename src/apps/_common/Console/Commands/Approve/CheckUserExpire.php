<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-11-29
 * Time: 21:45
 */

namespace Common\Console\Commands\Approve;


use Approve\Admin\Services\Order\AssignOrderService;
use Common\Models\Approve\ApproveUserPool;
use Illuminate\Console\Command;

class CheckUserExpire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approve:check-user-expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检查审批人员是否过期';

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
        AssignOrderService::getInstance()->checkExpired(ApproveUserPool::STATUS_BIND_TIMEOUT);
        $this->info('SUCCESS');
    }
}
