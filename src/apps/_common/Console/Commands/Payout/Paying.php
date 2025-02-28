<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Payout;

use Illuminate\Console\Command;

class Paying extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payout:check {date?}';
    protected $description = '手动放款成功 {date?}';

    public function handle() {
        $date = date("Y-m-d");
        if ($this->argument('date')) {
            $date = $this->argument('date');
        }
        \Common\Libraries\PayChannel\Paymob\PayoutHelper::helper()->pull('2022-02-27');
    }

}
