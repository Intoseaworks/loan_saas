<?php

namespace Common\Console\Commands;

use Common\Libraries\PayChannel\Fawry\RepayHelper;
use Common\Libraries\PayChannel\Paymob\PaymobBase;
use Common\Utils\Sms\SmsHelper;
use Illuminate\Console\Command;
use Common\Jobs\Crm\MarketingSmsJob;

/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-11-29
 * Time: 21:45
 */
//use Risk\Common\Models\Business\Order\Order;
//use Risk\Common\Models\Business\User\User;

class Test extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '测试';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle() {
        \Common\Libraries\PayChannel\Paymob\PayoutHelper::helper()->pull('2022-02-27');
    }

}
