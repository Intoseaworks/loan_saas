<?php

/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/2/14
 * Time: 17:14
 */

namespace Common\Console\Commands\Channel;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Common\Services\Third\AppflyerServer;
use Common\Utils\MerchantHelper;

class AppflySnap extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'channel:AppflySnap {--date=} {--appName=}';
    private $uri = "https://hq1.appsflyer.com/raw-data/export/{package_name}/in_app_events_report/v5?api_token=785d39a4-c487-41b9-8ea3-f853a4f03014&from={start_date}&to={end_date}&timezone=Asia%2fManila&additional_fields=device_model,keyword_id,store_reinstall,deeplink_url,oaid,install_app_store,contributor1_match_type,contributor2_match_type,contributor3_match_type,match_type,device_category,gp_referrer,gp_click_time,gp_install_begin,amazon_aid,keyword_match_type,att,conversion_type,campaign_type,is_lat&maximum_rows=1000000";
    private $uriU5 = "https://hq1.appsflyer.com/raw-data/export/com.u5.e_perash/in_app_events_report/v5?api_token=785d39a4-c487-41b9-8ea3-f853a4f03014&from={start_date}&to={end_date}&timezone=Asia%2fManila&additional_fields=device_model,keyword_id,store_reinstall,deeplink_url,oaid,install_app_store,contributor1_match_type,contributor2_match_type,contributor3_match_type,match_type,device_category,gp_referrer,gp_click_time,gp_install_begin,amazon_aid,keyword_match_type,att,conversion_type,campaign_type,is_lat&maximum_rows=1000000";
    private $uriU4 = "https://hq1.appsflyer.com/raw-data/export/com.SurityCash.hxov/in_app_events_report/v5?api_token=785d39a4-c487-41b9-8ea3-f853a4f03014&from={start_date}&to={end_date}&timezone=Asia%2fManila&additional_fields=device_model,keyword_id,store_reinstall,deeplink_url,oaid,install_app_store,contributor1_match_type,contributor2_match_type,contributor3_match_type,match_type,device_category,gp_referrer,gp_click_time,gp_install_begin,amazon_aid,keyword_match_type,att,conversion_type,campaign_type,is_lat&maximum_rows=1000000";
    private $packageName = [
        "u1" => ["name" => "com.lanhai.upesocash", "merchant_id" => "3"],
        "u3" => ["name" => "com.peralending.www", "merchant_id" => "6"],
        "u4" => ["name" => "com.SurityCash.hxov", "merchant_id" => "2"],
        "u5" => ["name" => "com.u5.e_perash", "merchant_id" => "1"],
        "u5-ios" => ["name" => 'id1567917703', "merchant_id" => "1"],
        "s2" => ["name" => "com.peranyo.cash.personal.loan.credit.peso.fast.lend.easy.quick.borrow.online.ph", "merchant_id" => "4"],
        "u6" => ["name" => 'com.Loanmoto.www', "merchant_id" => "7"],
    ];

    /**
     * The console command description.
     *
     * 可手动统计3天内数据
     * php artisan channel:count 2019-02-14
     *
     * @var string
     */
    protected $description = 'U5抓取appflyer渠道数据 --date=2021-10-20 --appName=u5';

    public function handle() {
        $endDate = Carbon::today()->toDateString();
        $merchantId = "1";
        $packageName = "id1567917703";
        if ($dateManual = $this->option('date')) {
            $endDate = $dateManual;
        }

        if ($appName = $this->option('appName')) {
            if(isset($this->packageName[$appName])){
                $merchantId = $this->packageName[$appName]['merchant_id'];
                $packageName = $this->packageName[$appName]['name'];
            }
        }

        # U5上线
        MerchantHelper::helper()->setMerchantId($merchantId);
        $startDate = Carbon::parse($endDate)->addDays(-1)->toDateString();

        $url = str_replace(['{package_name}','{start_date}', '{end_date}'], [$packageName, $startDate, $endDate], $this->uri);
        echo $url;
        echo "开始获取数据" . PHP_EOL;
        $dataList = $this->_request($url);
        #测试数据
//        $dataList = 'Attributed Touch Type,Attributed Touch Time,Install Time,Event Time,Event Name,Event Value,Event Revenue,Event Revenue Currency,Event Revenue USD,Event Source,Is Receipt Validated,Partner,Media Source,Channel,Keywords,Campaign,Campaign ID,Adset,Adset ID,Ad,Ad ID,Ad Type,Site ID,Sub Site ID,Sub Param 1,Sub Param 2,Sub Param 3,Sub Param 4,Sub Param 5,Cost Model,Cost Value,Cost Currency,Contributor 1 Partner,Contributor 1 Media Source,Contributor 1 Campaign,Contributor 1 Touch Type,Contributor 1 Touch Time,Contributor 2 Partner,Contributor 2 Media Source,Contributor 2 Campaign,Contributor 2 Touch Type,Contributor 2 Touch Time,Contributor 3 Partner,Contributor 3 Media Source,Contributor 3 Campaign,Contributor 3 Touch Type,Contributor 3 Touch Time,Region,Country Code,State,City,Postal Code,DMA,IP,WIFI,Operator,Carrier,Language,AppsFlyer ID,Advertising ID,IDFA,Android ID,Customer User ID,IMEI,IDFV,Platform,Device Type,OS Version,App Version,SDK Version,App ID,App Name,Bundle ID,Is Retargeting,Retargeting Conversion Type,Attribution Lookback,Reengagement Window,Is Primary Attribution,User Agent,HTTP Referrer,Original URL,Device Model,Keyword ID,Store Reinstall,Deeplink URL,OAID,Install App Store,Contributor 1 Match Type,Contributor 2 Match Type,Contributor 3 Match Type,Match Type,Device Category,Google Play Referrer,Google Play Click Time,Google Play Install Begin Time,Amazon Fire ID,Keyword Match Type,ATT,Conversion Type,Campaign Type,Is LAT
//click,2021-07-08 14:19:39,2021-07-08 14:44:29,2021-07-08 14:48:47,04_event_job_info,"{""04_event_job_info"":""9165709024##826301""}",,USD,,SDK,,pluckads,Facebook Ads,Facebook,,pluckads_Eperash_PH_0706_2.5_M1 - ios,23847937048550586,22-64_M1,23847937048630586,P-4,23847937048620586,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,PH,BUK,Kalasungay,8700,None,49.149.98.128,true,,,en-TR,1625725232155-7944042,,44C4F68C-0EB8-4A98-AB6B-8D8CD3051FD8,,,,79440421-A84C-4A1C-BE9C-AC00BA6D641D,ios,iPhone 6,12.5.4,2.0.2,v6.2.4,id1567917703,E-Perash -Philippines Loan App,com.u5.e-perash,false,,7d,,true,E-perash/26 CFNetwork/978.0.7 Darwin/18.7.0,,,iPhone6,,,,,,,,,srn,phone,,,,,,af_authorized,install,ua,
//click,2021-07-08 14:28:33,2021-07-08 14:32:14,2021-07-08 14:48:09,03_event_address,"{""03_event_address"":""9425627157##826275""}",,USD,,SDK,,pluckads,Facebook Ads,Facebook,,pluckads_Eperash_PH_0706_2.5_M1 - ios,23847937048550586,22-64_M1 - 广告副本,23847937048530586,P-4,23847937048560586,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,PH,00,San Juan Del Monte,1550,None,130.105.210.140,true,,,en-PH,1625725772577-1173012,,DFC19E8F-9B7E-4AE5-956E-594DE07805C2,,,,B11B73F0-1BA2-47EC-BE46-1D9438D858C5,ios,iPhone 6,12.5.1,2.0.2,v6.2.4,id1567917703,E-Perash -Philippines Loan App,com.u5.e-perash,false,,7d,,true,E-perash/26 CFNetwork/978.0.7 Darwin/18.7.0,,,iPhone6,,,,,,,,,srn,phone,,,,,,af_authorized,install,ua,
//click,2021-07-08 14:19:39,2021-07-08 14:44:29,2021-07-08 14:48:06,03_event_address,"{""03_event_address"":""9165709024##826301""}",,USD,,SDK,,pluckads,Facebook Ads,Facebook,,pluckads_Eperash_PH_0706_2.5_M1 - ios,23847937048550586,22-64_M1,23847937048630586,P-4,23847937048620586,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,PH,BUK,Kalasungay,8700,None,49.149.98.128,true,,,en-TR,1625725232155-7944042,,44C4F68C-0EB8-4A98-AB6B-8D8CD3051FD8,,,,79440421-A84C-4A1C-BE9C-AC00BA6D641D,ios,iPhone 6,12.5.4,2.0.2,v6.2.4,id1567917703,E-Perash -Philippines Loan App,com.u5.e-perash,false,,7d,,true,E-perash/26 CFNetwork/978.0.7 Darwin/18.7.0,,,iPhone6,,,,,,,,,srn,phone,,,,,,af_authorized,install,ua,
//click,2021-07-07 21:11:18,2021-07-08 11:21:32,2021-07-08 14:47:35,09_event_first_sign,"{""09_event_first_sign"":""9976348941##824857""}",,USD,,SDK,,pluckads,Facebook Ads,Facebook,,pluckads_Eperash_PH_0706_1.0_M1 - ios,23847936313900586,22-64_M1,23847936313910586,P-4,23847936313970586,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,PH,00,Makati,1200,None,110.54.192.104,false,,,en-PH,1625663502387-4684535,,1AB5EC9A-4141-488A-82EF-CEF69CE3FB85,,,,BE468453-BD53-47DE-BE50-DDBDCF766723,ios,iPhone 7,13.6.1,2.0.2,v6.2.4,id1567917703,E-Perash -Philippines Loan App,com.u5.e-perash,false,,7d,,true,E-perash/26 CFNetwork/1128.0.1 Darwin/19.6.0,,,iPhone7,,,,,,,,,srn,phone,,,,,,af_authorized,install,ua,
//click,2021-07-08 14:35:38,2021-07-08 14:41:23,2021-07-08 14:47:22,03_event_address,"{""03_event_address"":""9658822411##826285""}",,USD,,SDK,,pluckads,Facebook Ads,Facebook,,pluckads_Eperash_PH_0706_1.0_M1 - ios,23847936313900586,22-64_M1,23847936313910586,v-1,23847936314010586,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,PH,MSR,Cagayan De Oro,9000,None,49.146.41.83,true,,,en-PH,1625726368205-5511400,,6744177C-3B91-4EEF-8EEA-4AFCEDF45CE0,,,,5B5B1B14-00E1-40B7-B664-A3F5FFAE9B98,ios,iPhone 6 Plus,12.5.4,2.0.2,v6.2.4,id1567917703,E-Perash -Philippines Loan App,com.u5.e-perash,false,,7d,,true,E-perash/26 CFNetwork/978.0.7 Darwin/18.7.0,,,iPhone6Plus,,,,,,,,,srn,phone,,,,,,af_authorized,install,ua,
//click,2021-07-08 14:36:57,2021-07-08 14:44:25,2021-07-08 14:46:26,event_log_in,"{""event_log_in"":""9277186732##""}",,USD,,SDK,,pluckads,Facebook Ads,Facebook,,pluckads_Eperash_PH_0706_2.5_M1 - ios,23847937048550586,22-64_M1 - 广告副本,23847937048530586,P-4,23847937048560586,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,PH,PAM,Dolores,2001,None,122.2.97.82,true,,,en-JP,1625726248958-8375404,,AFF1DB36-1825-467E-A5AD-8C703C5220EE,,,,E8A3754F-EDF0-42DA-ADF3-2C6B59564F65,ios,iPhone 6,12.5.4,2.0.2,v6.2.4,id1567917703,E-Perash -Philippines Loan App,com.u5.e-perash,false,,7d,,true,E-perash/26 CFNetwork/978.0.7 Darwin/18.7.0,,,iPhone6,,,,,,,,,srn,phone,,,,,,af_authorized,install,ua,
//click,2021-07-08 14:19:39,2021-07-08 14:44:29,2021-07-08 14:46:21,02_event_identity,"{""02_event_identity"":""9165709024##""}",,USD,,SDK,,pluckads,Facebook Ads,Facebook,,pluckads_Eperash_PH_0706_2.5_M1 - ios,23847937048550586,22-64_M1,23847937048630586,P-4,23847937048620586,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,PH,BUK,Kalasungay,8700,None,49.149.98.128,true,,,en-TR,1625725232155-7944042,,44C4F68C-0EB8-4A98-AB6B-8D8CD3051FD8,,,,79440421-A84C-4A1C-BE9C-AC00BA6D641D,ios,iPhone 6,12.5.4,2.0.2,v6.2.4,id1567917703,E-Perash -Philippines Loan App,com.u5.e-perash,false,,7d,,true,E-perash/26 CFNetwork/978.0.7 Darwin/18.7.0,,,iPhone6,,,,,,,,,srn,phone,,,,,,af_authorized,install,ua,
//click,2021-07-08 14:19:39,2021-07-08 14:44:29,2021-07-08 14:45:15,01_loan_apply,"{""01_loan_apply"":""6964705743##""}",,USD,,SDK,,pluckads,Facebook Ads,Facebook,,pluckads_Eperash_PH_0706_2.5_M1 - ios,23847937048550586,22-64_M1,23847937048630586,P-4,23847937048620586,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,PH,BUK,Kalasungay,8700,None,49.149.98.128,true,,,en-TR,1625725232155-7944042,,44C4F68C-0EB8-4A98-AB6B-8D8CD3051FD8,,,,79440421-A84C-4A1C-BE9C-AC00BA6D641D,ios,iPhone 6,12.5.4,2.0.2,v6.2.4,id1567917703,E-Perash -Philippines Loan App,com.u5.e-perash,false,,7d,,true,E-perash/26 CFNetwork/978.0.7 Darwin/18.7.0,,,iPhone6,,,,,,,,,srn,phone,,,,,,af_authorized,install,ua,
//click,2021-07-08 14:19:39,2021-07-08 14:44:29,2021-07-08 14:45:09,event_log_in,"{""event_log_in"":""9165709024##""}",,USD,,SDK,,pluckads,Facebook Ads,Facebook,,pluckads_Eperash_PH_0706_2.5_M1 - ios,23847937048550586,22-64_M1,23847937048630586,P-4,23847937048620586,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,PH,BUK,Kalasungay,8700,None,49.149.98.128,true,,,en-TR,1625725232155-7944042,,44C4F68C-0EB8-4A98-AB6B-8D8CD3051FD8,,,,79440421-A84C-4A1C-BE9C-AC00BA6D641D,ios,iPhone 6,12.5.4,2.0.2,v6.2.4,id1567917703,E-Perash -Philippines Loan App,com.u5.e-perash,false,,7d,,true,E-perash/26 CFNetwork/978.0.7 Darwin/18.7.0,,,iPhone6,,,,,,,,,srn,phone,,,,,,af_authorized,install,ua,
//click,2021-07-08 14:19:39,2021-07-08 14:44:29,2021-07-08 14:44:29,00_event_init_agreement,"{""00_event_init_agreement"":""##""}",,USD,,SDK,,pluckads,Facebook Ads,Facebook,,pluckads_Eperash_PH_0706_2.5_M1 - ios,23847937048550586,22-64_M1,23847937048630586,P-4,23847937048620586,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,PH,BUK,Kalasungay,8700,None,49.149.98.128,true,,,en-TR,1625725232155-7944042,,44C4F68C-0EB8-4A98-AB6B-8D8CD3051FD8,,,,79440421-A84C-4A1C-BE9C-AC00BA6D641D,ios,iPhone 6,12.5.4,2.0.2,v6.2.4,id1567917703,E-Perash -Philippines Loan App,com.u5.e-perash,false,,7d,,true,E-perash/26 CFNetwork/978.0.7 Darwin/18.7.0,,,iPhone6,,,,,,,,,srn,phone,,,,,,af_authorized,install,ua,
//click,2021-07-08 14:36:57,2021-07-08 14:44:25,2021-07-08 14:44:28,00_event_init_agreement,"{""00_event_init_agreement"":""##""}",,USD,,SDK,,pluckads,Facebook Ads,Facebook,,pluckads_Eperash_PH_0706_2.5_M1 - ios,23847937048550586,22-64_M1 - 广告副本,23847937048530586,P-4,23847937048560586,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,PH,PAM,Dolores,2001,None,122.2.97.82,true,,,en-JP,1625726248958-8375404,,AFF1DB36-1825-467E-A5AD-8C703C5220EE,,,,E8A3754F-EDF0-42DA-ADF3-2C6B59564F65,ios,iPhone 6,12.5.4,2.0.2,v6.2.4,id1567917703,E-Perash -Philippines Loan App,com.u5.e-perash,false,,7d,,true,E-perash/26 CFNetwork/978.0.7 Darwin/18.7.0,,,iPhone6,,,,,,,,,srn,phone,,,,,,af_authorized,install,ua,
//click,2021-07-07 21:11:18,2021-07-08 11:21:32,2021-07-08 14:44:12,06_event_pay_info,"{""06_event_pay_info"":""9976348941##824857""}",,USD,,SDK,,pluckads,Facebook Ads,Facebook,,pluckads_Eperash_PH_0706_1.0_M1 - ios,23847936313900586,22-64_M1,23847936313910586,P-4,23847936313970586,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,PH,00,Makati,1200,None,110.54.192.104,false,,,en-PH,1625663502387-4684535,,1AB5EC9A-4141-488A-82EF-CEF69CE3FB85,,,,BE468453-BD53-47DE-BE50-DDBDCF766723,ios,iPhone 7,13.6.1,2.0.2,v6.2.4,id1567917703,E-Perash -Philippines Loan App,com.u5.e-perash,false,,7d,,true,E-perash/26 CFNetwork/1128.0.1 Darwin/19.6.0,,,iPhone7,,,,,,,,,srn,phone,,,,,,af_authorized,install,ua,
//click,2021-07-08 14:35:38,2021-07-08 14:41:23,2021-07-08 14:44:11,02_event_identity,"{""02_event_identity"":""9658822411##""}",,USD,,SDK,,pluckads,Facebook Ads,Facebook,,pluckads_Eperash_PH_0706_1.0_M1 - ios,23847936313900586,22-64_M1,23847936313910586,v-1,23847936314010586,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,PH,MSR,Cagayan De Oro,9000,None,49.146.41.83,true,,,en-PH,1625726368205-5511400,,6744177C-3B91-4EEF-8EEA-4AFCEDF45CE0,,,,5B5B1B14-00E1-40B7-B664-A3F5FFAE9B98,ios,iPhone 6 Plus,12.5.4,2.0.2,v6.2.4,id1567917703,E-Perash -Philippines Loan App,com.u5.e-perash,false,,7d,,true,E-perash/26 CFNetwork/978.0.7 Darwin/18.7.0,,,iPhone6Plus,,,,,,,,,srn,phone,,,,,,af_authorized,install,ua,
//';
        $dataList = explode("\n", $dataList);


        $newData = [];
        if (is_array($dataList)) {
            $title = str_getcsv($dataList[0]);
            unset($dataList[0]);
            echo "共获取" . count($dataList) . "开始处理" . PHP_EOL;
            foreach ($title as $key => $item) {
                $title[$key] = strtolower(str_replace(" ", "_", trim($item)));
            }
            echo "标题处理完毕,开始入库" . PHP_EOL;
            foreach ($dataList as $item) {
                $data = str_getcsv($item);
                if (is_array($data) && count($data) == count($title)) {
                    $newData = array_combine($title, $data);
                    AppflyerServer::server()->create($newData);
                    echo "#";
                }
            }
            echo "<===END" . PHP_EOL;
        }
    }

    private function _request($uri, $headerData = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        if ($headerData)
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

}
