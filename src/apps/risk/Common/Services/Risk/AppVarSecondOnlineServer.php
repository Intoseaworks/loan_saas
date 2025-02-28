<?php

namespace Risk\Common\Services\Risk;

use Common\Services\BaseService;
use Risk\Common\Models\UserData\UserApplicationDiffCreateInstall;
use Risk\Common\Services\NewRisk\RiskUserAppServer;
use DB;

class AppVarSecondOnlineServer extends BaseService {

    public function run($order, $initData) {
        $data = [];
        # shop
        $data[] = [$order->user_id, $order->id, "shop", $initData['shopAppCount'], 0];
        # nsystemapp_in_m1
        $data[] = [$order->user_id, $order->id, "nsystemapp_in_m1", $initData['notSystemappCountInMonth'], 0];
        # nsys_shop_lt_m1
        $data[] = [$order->user_id, $order->id, "nsys_shop_lt_m1", $initData['shopAppOverMonthCount'], 0];
//        # sb_app
        $data[] = [$order->user_id, $order->id, "sb_app", $initData['sbAppCount'], 0];
//        # up_app
        $data[] = [$order->user_id, $order->id, "up_app", $initData['upAppCount'], 0];
        //nsys_loan_ud_d1
        $initData['nsys_loan_ud_d1'] = UserApplicationDiffCreateInstall::model()
        ->whereOrderId($order->id)
        ->whereIsAdjSystem(0)
        ->whereIsLoan(1)->where('dif_create_update','<=',1)->count();
        $data[] = [$order->user_id, $order->id, "nsys_loan_ud_d1",$initData['nsys_loan_ud_d1'],0];
        //nsys_loan_ud_d7
        $initData['nsys_loan_ud_d7'] = UserApplicationDiffCreateInstall::model()
        ->whereOrderId($order->id)
        ->whereIsAdjSystem(0)
        ->whereIsLoan(1)->where('dif_create_update','<=',7)->count();
        $data[] = [$order->user_id, $order->id, "nsys_loan_ud_d7",$initData['nsys_loan_ud_d7'],0];
        //nsys_loan_ud_m1
        $initData['nsys_loan_ud_m1'] = UserApplicationDiffCreateInstall::model()
        ->whereOrderId($order->id)
        ->whereIsAdjSystem(0)
        ->whereIsLoan(1)->where('dif_create_update','<=',30)->count();
        $data[] = [$order->user_id, $order->id, "nsys_loan_ud_m1",$initData['nsys_loan_ud_m1'],0];
        $min_int_bad_app = UserApplicationDiffCreateInstall::model()->select(\DB::raw('min(dif_create_install) as mindif'))
            ->whereOrderId($order->id)
            ->whereIsAdjSystem(0)
            ->whereIsBad(1)->first();
        $initData['min_int_bad_app'] = optional($min_int_bad_app)->mindif;
        $data[] = [$order->user_id, $order->id, "min_int_bad_app",$initData['min_int_bad_app'],0];
//        # up_p,sb_p
        $initData['nsys_loan_ud_d1_ar'] = $initData['nsys_loan_ud_m1_ar'] = $initData['up_p'] = $initData['sb_p'] = null;
        if ( $initData['Nsystemapp'] ){
            $initData['up_p'] = round(100*$initData['upAppCount']/$initData['Nsystemapp'],2);
            $data[] = [$order->user_id, $order->id, "up_p", $initData['up_p'], 0];
            $initData['sb_p'] = round(100*$initData['sbAppCount']/$initData['Nsystemapp'],2);
            $data[] = [$order->user_id, $order->id, "sb_p", $initData['sb_p'], 0];
            $initData['nsys_loan_ud_d1_ar'] = round(100*$initData['nsys_loan_ud_d1']/$initData['Nsystemapp'],2);
            $data[] = [$order->user_id, $order->id, "nsys_loan_ud_d1_ar", $initData['nsys_loan_ud_d1_ar'], 0];
            $initData['nsys_loan_ud_m1_ar'] = round(100*$initData['nsys_loan_ud_m1']/$initData['Nsystemapp'],2);
            $data[] = [$order->user_id, $order->id, "nsys_loan_ud_m1_ar", $initData['nsys_loan_ud_m1_ar'], 0];
        }
        $initData['nsys_loan_ud_d1_r'] = $initData['nsys_loan_ud_d7_r'] = null;
        if ( $initData['Nsys_loan'] ){
            $initData['nsys_loan_ud_d1_r'] = round(100*$initData['nsys_loan_ud_d1']/$initData['Nsys_loan'],2);
            $data[] = [$order->user_id, $order->id, "nsys_loan_ud_d1_r", $initData['nsys_loan_ud_d1_r'], 0];
            $initData['nsys_loan_ud_d7_r'] = round(100*$initData['nsys_loan_ud_d7']/$initData['Nsys_loan'],2);
            $data[] = [$order->user_id, $order->id, "nsys_loan_ud_d7_r", $initData['nsys_loan_ud_d7_r'], 0];
        }
        $std_loan_install_dif = \DB::connection('mysql_risk')->table('risk_user_application_diff_create_install')
            ->select(\DB::raw('std(loan_install_diff) as std_loan_install_diff'))
            ->whereIsLoan(1)
            ->whereOrderId($order->id)
            ->first();
        $data[] = [$order->user_id, $order->id, "std_loan_install_dif", $std_loan_install_dif->std_loan_install_diff, 0];
        return $data;
    }

    public function getInitData($app, $initData, RiskUserAppServer $riskUserAppServer) {
        $initData = $riskUserAppServer->upAppCount($app, $initData);
        $initData = $riskUserAppServer->sbAppCount($app, $initData);
        $initData = $riskUserAppServer->notSystemappCountInMonth($initData['is_adj_system'],$initData['dif_create_install'], $initData);
        $initData = $riskUserAppServer->shopAppCount($app, $initData);
        $initData = $riskUserAppServer->shopAppOverMonthCount($app,$initData['is_adj_system'],$initData['dif_create_install'], $initData);
        $initData['userApplicationDiffCreateInstall']->save();
        return $initData;
    }

}
