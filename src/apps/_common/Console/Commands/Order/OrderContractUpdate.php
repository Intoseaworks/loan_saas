<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/31
 * Time: 15:42
 */

namespace Common\Console\Commands\Order;

use Common\Services\OrderAgreement\OrderAgreementServer;
use Common\Models\Order\ContractAgreement;
use Illuminate\Console\Command;

class OrderContractUpdate extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:contract-update {--oid=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '订单合同更新';

    public function handle() {
        $oid = $this->option('oid');
        if ($oid) {
            $this->updateById($oid);
        }
        echo 'Over';
    }

    protected function updateById($oid) {

        $oids = [$oid];
        foreach ($oids as $orderId) {
            echo $orderId . PHP_EOL;
            OrderAgreementServer::server()->generate($orderId, ContractAgreement::CASHNOW_LOAN_CONTRACT, true, false, '', true);
        }
    }

    protected function contractUpdate() {
        //set_time_limit(0);
        /* $contractAgreements = ContractAgreement::query()->where('id', '<=', 11546)->where('status', ContractAgreement::STATUS_ACTIVE)->get();
          foreach ($contractAgreements as $contractAgreement) {
          echo $contractAgreement->id.PHP_EOL;
          OrderAgreementServer::server()->generate($contractAgreement->order_id, ContractAgreement::CASHNOW_LOAN_CONTRACT, true, false, '', true);
          } */
        //$count = true;
        //while ($count){
        $contractAgreements = ContractAgreement::query()
                ->where('id', '<=', 11546)
                ->where('status', ContractAgreement::STATUS_ACTIVE)
                ->orderBy('id', 'asc')
                ->limit(100)
                ->pluck('order_id', 'id');
//            if(count($contractAgreements) == 0) {
//                $count = 0;
//                break;
//            }
        foreach ($contractAgreements as $id => $orderId) {
            echo $id . PHP_EOL;
            OrderAgreementServer::server()->generate($orderId, ContractAgreement::CASHNOW_LOAN_CONTRACT, true, false, '', true);
        }
        //}
    }

}
