<?php

namespace Risk\Common\Jobs;

use Cache;
use Carbon\Carbon;
use Common\Utils\Data\StringHelper;
use Common\Utils\DingDing\DingHelper;
use Risk\Common\Models\Business\RiskData\RiskDataIndex;
use Risk\Common\Models\UserData\UserApp;
use Risk\Common\Models\UserData\UserApplicationDiffCreateInstall;
use Risk\Common\Services\NewRisk\RiskReloanUserAppServer;

class ComputeUserapplicationDifCreateInstall
{
    public $queue = 'risk-default';

    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 3;

    public $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function handle()
    {
        try {
            echo ($this->order->id);
            $startTime = date("Y-m-d H:i:s");
            $applicationIds = UserApp::model()->select(\DB::raw('max(id) as idmax'))
                ->whereOrderId($this->order->id)
                ->groupBy('pkg_name')
                ->pluck('idmax')->toArray();
            if ( is_array($applicationIds)  && count($applicationIds) >0 ){
                $applications = UserApp::model()->whereIn('id',$applicationIds)->get();
            }else{
                return;
            }
            $riskUserAppServer = RiskReloanUserAppServer::server();
            $systemappCount = 0;
            $notSystemappCount = 0;
            $loanappCount = 0;
            $gloanappCount = 0;
            $prmappCount = 0;
            $gloan_in_d1 = 0;
            $initData = [];
            foreach ( $applications as $application ){
                $is_adj_system = 0;
                if ( $application->installed_time >= '2012-01-01' ){
                    $dif_create_install = round((strtotime($application->created_at)-strtotime($application->installed_time))/3600/24, 5);
                }else{
                    $dif_create_install = null;
                }
                if ( $application->updated_time >= '2012-01-01' ){
                    $dif_create_update = round((strtotime($application->created_at)-strtotime($application->updated_time))/3600/24, 5);
                }else{
                    $dif_create_update = null;
                }
                $data = ['user_application_id'=>$application->id,'dif_create_install'=>$dif_create_install,'order_id'=>$application->order_id,
                    'dif_create_update'=>$dif_create_update,'pkg_name'=>$application->pkg_name];
                $data['installed_time'] = $application->installed_time;
                $data['collection_time'] = $application->created_at;
                //数据转化统一
                $application->small_pkg_name = strtolower($application->pkg_name);
                $application->small_app_name = StringHelper::clearSpaceToLower($application->app_name);
                //adj_system数量计算
                if ( $riskUserAppServer->isSysType($application) ) {
                    $systemappCount ++;
                    $is_adj_system = 1;
                }else{
                    $notSystemappCount ++;
                }
                $initData['is_adj_system'] = $data['is_adj_system'] = $is_adj_system;
                $initData['dif_create_install'] = $dif_create_install;
                $initData['dif_create_update'] = $dif_create_update;
                $userApplicationDiffCreateInstall = UserApplicationDiffCreateInstall::updateOrCreateModel($data,['user_application_id'=>$application->id,'order_id'=>$application->order_id]);
                $initData['is_loan'] = $initData['is_gloan'] = $initData['nsys_is_gloan'] = 0;
                if ( $riskUserAppServer->isLoanType($application) ) {
                    $loanappCount ++;
                    $userApplicationDiffCreateInstall->loan_install_diff = $dif_create_install;
                    $userApplicationDiffCreateInstall->is_loan = 1;
                    $initData['is_loan'] = 1;
                    //nsys_loan
                    if ( !$is_adj_system ){
                        $initData['is_nsys_loan'] = 1;
                        $userApplicationDiffCreateInstall->is_nsys_loan = 1;
                    }
                }
                if ( $riskUserAppServer->isGloanType($application) ) {
                    $userApplicationDiffCreateInstall->is_gloan = 1;
                    $gloanappCount ++;
                    //gloan_in_d1
                    if ( $dif_create_install <= 1 ){
                        $gloan_in_d1 ++;
                    }
                    $initData['is_gloan'] = 1;
                    //nsys_gloan
                    if ( !$is_adj_system ){
                        $initData['nsys_is_gloan'] = 1;
                        $userApplicationDiffCreateInstall->is_nsys_gloan = 1;
                    }
                }
                if ( $riskUserAppServer->isPrmType($application) ) {
                    $userApplicationDiffCreateInstall->is_prm = 1;
                    $prmappCount ++;
                }
                $userApplicationDiffCreateInstall->is_adj_system = $is_adj_system;
                $userApplicationDiffCreateInstall->save();
                $initData['userApplicationDiffCreateInstall'] = $userApplicationDiffCreateInstall;
                $initData = \Risk\Common\Services\Risk\AppVarOnlineServer::server()->discrete($application, $initData, $riskUserAppServer);
                $initData = \Risk\Common\Services\Risk\AppVarSecondOnlineServer::server()->getInitData($application, $initData, $riskUserAppServer);
            }
            $gloan_in_d1_r = null;
            $prm_p = null;
            if ( $gloanappCount ){
                $gloan_in_d1_r = round(100*$gloan_in_d1/$gloanappCount,2);
            }
            if ( $notSystemappCount ){
                $prm_p = round(100*$prmappCount/$notSystemappCount,2);
            }
            $appNum = UserApp::model()->whereOrderId($this->order->id)->groupby('pkg_name')->get()->count();
            $initData['Nsystemapp'] = $notSystemappCount;
            $initData['gloan'] = $gloanappCount;
            $initData['loan'] = $loanappCount;
            $initData['app_num'] = $appNum;
            $initData['prm_app'] = $prmappCount;
            $riskDataIndexs = \Risk\Common\Services\Risk\AppVarOnlineServer::server()->run($this->order, $initData);
            $riskDataSecondIndexs = \Risk\Common\Services\Risk\AppVarSecondOnlineServer::server()->run($this->order, $initData);
            $riskDataIndexs = array_merge($riskDataIndexs,$riskDataSecondIndexs);
            $riskDataIndexs[] = [$this->order->user_id,$this->order->id,"adj_system",$systemappCount];
            $riskDataIndexs[] = [$this->order->user_id,$this->order->id,"loan",$loanappCount];
            $riskDataIndexs[] = [$this->order->user_id,$this->order->id,"app_num",$appNum];
            $riskDataIndexs[] = [$this->order->user_id,$this->order->id,"gloan",$gloanappCount];
            $riskDataIndexs[] = [$this->order->user_id,$this->order->id,"prm_app",$prmappCount];
            $riskDataIndexs[] = [$this->order->user_id,$this->order->id,"Nsystemapp",$notSystemappCount];
            $riskDataIndexs[] = [$this->order->user_id,$this->order->id,"gloan_in_d1",$gloan_in_d1];
            $riskDataIndexs[] = [$this->order->user_id,$this->order->id,"gloan_in_d1_r",$gloan_in_d1_r];
            $riskDataIndexs[] = [$this->order->user_id,$this->order->id,"prm_p",$prm_p];
            $key = "Order::std_create_install_dif::OrderID::{$this->order->id}".date("Ymd");
            $std_create_install_dif = Cache::remember($key, 10, function(){
                return \DB::connection('mysql_risk')->table('risk_user_application_diff_create_install')
                ->select(\DB::raw('std(dif_create_install) as std_create_install_dif, std(dif_create_update) as std_dif_create_update'))
                ->whereIsAdjSystem(0)
                ->whereOrderId($this->order->id)
                ->first();
            });
            // $std_create_install_dif = \DB::connection('mysql_risk')->table('risk_user_application_diff_create_install')
            //     ->select(\DB::raw('std(dif_create_install) as std_create_install_dif, std(dif_create_update) as std_dif_create_update'))
            //     ->whereIsAdjSystem(0)
            //     ->whereOrderId($this->order->id)
            //     ->first();
            $riskDataIndexs[] = [$this->order->user_id,$this->order->id,"std_create_install_dif",$std_create_install_dif->std_create_install_dif];
            $riskDataIndexs[] = [$this->order->user_id,$this->order->id,"std_dif_create_update",$std_create_install_dif->std_dif_create_update];
            $riskDataIndexs[] = [$this->order->user_id,$this->order->id,"start_time", $startTime];
            $riskDataIndexs[] = [$this->order->user_id,$this->order->id,"end_time",date("Y-m-d H:i:s")];
            $this->insertRiskDataIndexs($riskDataIndexs);
            // return $riskDataIndexs;
        } catch (\Exception $exception) {
            // echo $exception->getMessage();
            // exit;
            DingHelper::notice($this->order->id . 'ERROR:' . $exception->getMessage(), '计算dif_create_install队列抛错');
        }

    }

    public function insertRiskDataIndexs($riskDataIndexs)
    {
        foreach ( $riskDataIndexs as $riskDataIndex ){
            $insertData = [
                "user_id" => $riskDataIndex[0],
                "order_id" => $riskDataIndex[1],
                "index_name" => $riskDataIndex[2],
                "index_value" => $riskDataIndex[3],
            ];
            $relateData = [
                "user_id" => $riskDataIndex[0],
                "order_id" => $riskDataIndex[1],
                "index_name" => $riskDataIndex[2],
            ];
            RiskDataIndex::updateOrCreate($relateData, $insertData);
        }
    }
}
