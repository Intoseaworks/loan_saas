<?php

namespace Risk\Common\Services\Risk;

use Common\Services\BaseService;
use Risk\Common\Models\UserData\UserApplicationDiffCreateInstall;
use Risk\Common\Services\NewRisk\RiskUserAppServer;
use DB;

class AppVarOnlineServer extends BaseService {

    public function run($order, $initData) {
        echo 'insert';
        $data = [];
        # max_daily_instl_apps_int
//        $sql = "select date(installed_time) as install_time,count(1) c,max(collection_time) as collection_time, min(ceil(dif_create_install)) as max_daily_install_apps from risk_user_application_diff_create_install where order_id={$order->id} AND is_adj_system=0
// and installed_time >='2012-01-01' GROUP BY date(installed_time) ORDER BY c DESC,max_daily_install_apps";
        $sql = "SELECT
	order_id,
	CEIL( dif_create_install ) AS dif_create_install,
	COUNT( 1 ) AS nums
FROM
	`risk_user_application_diff_create_install`
WHERE
	order_id = {$order->id}
	AND is_adj_system = 0
	AND installed_time >= '2012-01-01'
GROUP BY
	1,
	2
ORDER BY
	nums DESC,
	dif_create_install";
        $res = DB::connection('mysql_risk')->select($sql);
        $data[] = [$order->user_id, $order->id, "max_daily_instl_apps_int", $res[0]->dif_create_install, 0];
        # btm_app
        $data[] = [$order->user_id, $order->id, "btm_app", $initData['btm_app'], 0];
        # btm_p
        $btm_p = $initData['Nsystemapp'] == 0 ? null : round(100 * $initData['btm_app'] / $initData['Nsystemapp'], 2);
        $data[] = [$order->user_id, $order->id, "loan_p", $btm_p, 0];
        # bank
        $data[] = [$order->user_id, $order->id, "bank", $initData['bank_app_count'], 0];
        # pay
        $data[] = [$order->user_id, $order->id, "pay", $initData['pay_app_count'], 0];
        # good_app
        $data[] = [$order->user_id, $order->id, "good_app", $initData['good_app_count'], 0];
        # nsys_good_app
        $data[] = [$order->user_id, $order->id, "nsys_good_app", $initData['nsys_good_app_count'], 0];
        # nsys_good_app_lt_m1
        $data[] = [$order->user_id, $order->id, "nsys_good_app_lt_m1", $initData['nsys_good_app_lt_m1'], 0];
        # Nsys_loan
        $data[] = [$order->user_id, $order->id, "Nsys_loan", $initData['Nsys_loan'], 0];
        # nsys_loan_in_d1
        $nsys_loan_in_d1 = UserApplicationDiffCreateInstall::model()
            ->whereOrderId($order->id)
            ->whereIsAdjSystem(0)
            ->whereIsLoan(1)
            ->where('dif_create_install','<=',1)->count();
        $initData['nsys_loan_in_d1'] = $nsys_loan_in_d1;
        $data[] = [$order->user_id, $order->id, "nsys_loan_in_d1", $initData['nsys_loan_in_d1'], 0];
        # nsys_loan_in_d1_ar
        $nsys_loan_in_d1_ar = null;
        if ( $initData['Nsystemapp'] ){
            $nsys_loan_in_d1_ar = $initData['nsys_loan_in_d1'] == 0 ? 0 : round(100 * $initData['nsys_loan_in_d1'] / $initData['Nsystemapp'], 2);
        }
        $data[] = [$order->user_id, $order->id, "nsys_loan_in_d1_ar", $nsys_loan_in_d1_ar, 0];
        # nsys_loan_in_d7
         $nsys_loan_in_d7 = UserApplicationDiffCreateInstall::model()
         ->whereOrderId($order->id)
         ->whereIsAdjSystem(0)
         ->whereIsLoan(1)
         ->where('dif_create_install','<=',7)->count();
        $initData['nsys_loan_in_d7'] = $nsys_loan_in_d7;
        $data[] = [$order->user_id, $order->id, "nsys_loan_in_d7", $initData['nsys_loan_in_d7'], 0];
        # max_int_loanist
        $data[] = [$order->user_id, $order->id, "max_int_loanist", $initData['max_int_loanist'], 0];
        # bad_app
        $data[] = [$order->user_id, $order->id, "bad_app", $initData['bad_app_count'], 0];
        # new_good_app
        $data[] = [$order->user_id, $order->id, "new_good_app", $initData['new_good_app'], 0];
        # gloan_in_m0
        $data[] = [$order->user_id, $order->id, "gloan_in_m0", round(100 * $initData['btm_app'] / $initData['Nsystemapp'], 2), 0];
        # gloan_in_m1
        $data[] = [$order->user_id, $order->id, "gloan_in_m1", $initData['gloan_in_m1'], 0];
        # gloan_p
        $gloan_p = $initData['loan'] == 0 ? null : round(100 * $initData['gloan'] / $initData['loan'], 2);
        $data[] = [$order->user_id, $order->id, "gloan_p", $gloan_p, 0];
        # loan_in_d7
        $data[] = [$order->user_id, $order->id, "loan_in_d7", $initData['loan_in_d7'], 0];
        # loan_in_d7_r
        $loan_in_d7_r = $initData['loan'] == 0 ? null : round(100 * $initData['loan_in_d7'] / $initData['loan'], 2);
        $data[] = [$order->user_id, $order->id, "loan_in_d7_r", $loan_in_d7_r, 0];
        # loan_in_m1
        $data[] = [$order->user_id, $order->id, "loan_in_m1", $initData['loan_in_m1'], 0];
        # nsys_gloan_in_m1
        $data[] = [$order->user_id, $order->id, "nsys_gloan_in_m1", $initData['nsys_gloan_in_m1'], 0];
        # loan_in_m1_r
        $loan_in_m1_r = $initData['loan'] == null || $initData['loan_in_m1']==0 ? 0 : round(100 * $initData['loan_in_m1'] / $initData['loan'], 2);
        $data[] = [$order->user_id, $order->id, "loan_in_m1_r", $loan_in_m1_r, 0];
        # loan_p
        $loan_p = $initData['Nsystemapp'] ==0 ? null : round(100 * UserApplicationDiffCreateInstall::model()->whereOrderId($order->id)->whereIsNsysLoan(1)->count() / $initData['Nsystemapp'], 2);
        $data[] = [$order->user_id, $order->id, "loan_p", $loan_p, 0];
        return $data;
    }

    public function discrete($app, $initData, RiskUserAppServer $riskUserAppServer) {
        # btm_app_count
        $initData['btm_app'] = $initData['btm_app'] ?? 0;
        if ($riskUserAppServer->isBtmType($app)) {
            $initData['userApplicationDiffCreateInstall']->is_btm = 1;
            $initData['btm_app']++;
        }
        # bank
        $initData['bank_app_count'] = $initData['bank_app_count'] ?? 0;
        if ($riskUserAppServer->isBankType($app)) {
            $initData['userApplicationDiffCreateInstall']->is_bank = 1;
            $initData['bank_app_count']++;
        }
        # pay
        $initData['pay_app_count'] = $initData['pay_app_count'] ?? 0;
        if ($riskUserAppServer->isPayType($app)) {
            $initData['userApplicationDiffCreateInstall']->is_pay = 1;
            $initData['pay_app_count']++;
        }
        # good
        $initData['good_app_count'] = $initData['good_app_count'] ?? 0;
        $initData['nsys_good_app_count'] = $initData['nsys_good_app_count'] ?? 0;
        $initData['nsys_good_app_lt_m1'] = $initData['nsys_good_app_lt_m1'] ?? 0;
        if ($riskUserAppServer->isGoodType($app)) {
            $initData['userApplicationDiffCreateInstall']->is_good = 1;
            $initData['good_app_count']++;
            if ( !$initData['is_adj_system'] ){
                $initData['userApplicationDiffCreateInstall']->is_nsys_good = 1;
                $initData['nsys_good_app_count'] ++;
                if ( $initData['dif_create_install'] > 30 ){
                    $initData['nsys_good_app_lt_m1'] ++;
                }
            }
        }
        # Nsys_loan
        $initData['Nsys_loan'] = $initData['Nsys_loan'] ?? 0;
//        $initData['nsys_loan_in_d1'] = $initData['nsys_loan_in_d1'] ?? 0;
        $initData['nsys_loan_in_d7'] = $initData['nsys_loan_in_d7'] ?? 0;
        if ($riskUserAppServer->isLoanType($app) && !$initData['is_adj_system']) {
            $initData['Nsys_loan']++;
//            if ( $initData['dif_create_install'] <= 1 ){
//                $initData['nsys_loan_in_d1'] ++;
//            }
            // if ( $initData['dif_create_install'] <= 7 ){
            //     $initData['nsys_loan_in_d7'] ++;
            // }
        }
        # max_int_loanist
        $initData['max_int_loanist'] = $initData['max_int_loanist'] ?? 0;
        if ($riskUserAppServer->isLoanType($app) && !$initData['is_adj_system']) {
            $initData['max_int_loanist'] = max($initData['max_int_loanist'], $initData['dif_create_install']);
        }
        # bad_app
        $initData['bad_app_count'] = $initData['bad_app_count'] ?? 0;
        if ($riskUserAppServer->isBadType($app)) {
            $initData['userApplicationDiffCreateInstall']->is_bad = 1;
            $initData['bad_app_count']++;
        }

        # min_int_bad_app
//        $initData['min_int_bad_app'] = $initData['min_int_bad_app'] ?? $initData['dif_create_install'];
//        if ($riskUserAppServer->isBadType($app) && !$initData['is_adj_system']) {
//            $initData['min_int_bad_app'] = min($initData['min_int_bad_app'], $initData['dif_create_install']);
//        }

        # new good
        $initData['new_good_app'] = $initData['new_good_app'] ?? 0;
        if ($riskUserAppServer->isNewGoodType($app)) {
            $initData['userApplicationDiffCreateInstall']->is_new_good = 1;
            $initData['new_good_app']++;
        }
        # gloan_in_m1
        $initData['gloan_in_m1'] = $initData['gloan_in_m1'] ?? 0;
        $initData['nsys_gloan_in_m1'] = $initData['nsys_gloan_in_m1'] ?? 0;
        if (isset($initData['dif_create_install']) && $initData['dif_create_install'] != '' && $initData['dif_create_install'] <= 30) {
            $initData['gloan_in_m1'] += $initData['is_gloan'];
        }
        if (isset($initData['dif_create_install']) && $initData['dif_create_install'] != '' && $initData['dif_create_install'] <= 30 && $initData['nsys_is_gloan']) {
            $initData['nsys_gloan_in_m1'] += $initData['nsys_is_gloan'];
        }
        # loan_in_d7
        $initData['loan_in_d7'] = $initData['loan_in_d7'] ?? 0;
        if (isset($initData['dif_create_install']) && $initData['dif_create_install'] != '' && $initData['dif_create_install'] <= 7) {
            $initData['loan_in_d7'] += $initData['is_loan'];
        }
        # loan_in_m1
        $initData['loan_in_m1'] = $initData['loan_in_m1'] ?? 0;
        if (isset($initData['dif_create_install']) && $initData['dif_create_install'] != '' && $initData['dif_create_install'] <= 30) {
            $initData['loan_in_m1'] += $initData['is_loan'];
        }
        $initData['userApplicationDiffCreateInstall']->save();
        return $initData;
    }

}
