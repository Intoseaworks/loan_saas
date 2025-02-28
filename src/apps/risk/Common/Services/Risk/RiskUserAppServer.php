<?php

namespace Risk\Common\Services\Risk;

//use Risk\Common\Models\Business\UserData\UserApplication;
use Risk\Common\Models\UserData\UserApp as UserApplication;
use Risk\Common\Models\Business\Order\Order;
use Illuminate\Support\Facades\Cache;
use Common\Services\BaseService;
use Common\Utils\MerchantHelper;
use Risk\Common\Models\Business\RiskData\RiskDataIndex;
use Risk\Common\Models\Business\UserData\UserContactsTelephone;

class RiskUserAppServer extends BaseService {

    const CACHE_TIME = 1;
    const CACHE_EKEY = "C"; //扩展key 修改后清空缓存
    /*     * 贷款类大写包名* */
    const LOAN_APP_PKG_NAME_KEYWORDS = <<<LIST
CASH
LOAN
RUPEE
BUS
CREDIT
LIST;
    /*     * 贷款类大写名* */
    const LOAN_APP_NAME_KEYWORDS = <<<LIST
CASH
LOAN
RUPEE
BUS
CREDIT
LIST;
    /*     * 差的贷款类大写包名* */
    const BAD_LOAN_APP_PKG_NAME_KEYWORDS = <<<LIST
INSTATPERSONALLOANS.PERSONALLOANCONSULATIONS
INSTANTLOAN.PERSONALLOAN.ONLINELOAN.LOANONAADHARCARD.PHOTOAPPSTORE
COM.QUIKKLOAN.ANDROID852220
COM.OFFERMELOAN.APP.OFFERMELOAN
COM.LOANIT.APP
COM.MYLOANTIGER.RETAPP
COM.INDIA.ILOAN
ENTERSTUDIO.INSTAPERSONALLOAN.PERSONALLOANONLINE
COM.MKTLOAN.MONEYMARKET
COM.VAY.LOAN.EASYMONEY.CREDIT.MASTER.RUPPER
COM.INDIA.FASTCASH
COM.ZONIE.INSTANT.LOAN
COM.RCMBUSINESS
JBSD.ANYLOAN
COME.SNAPCASHIN.APP
COM.UDHAARLOAN.UDHAAR_E
COM.CIDI.EASYCASH
COM.IZWE.PESALOANS.ONLINE
IND.ZEBRALOAN.BLACK
COM.APP.LOANMANAGER
COM.AMOTECH.STARLOAN
COM.PCCOMPUTERAMRELI.CASH_CALCULATOR
IN.ONECASH.APP
COM.BUSINESS.CAPITALTRADE
COM.MAINTAIN.KAMALOANPOCKET
COM.CASHAAJ
AIR.COM.ACE2THREE.MOBILE.CASH
COM.CASHNOW.ANDROID
COM.SUNX.KHASHLOAN
COM.FAVORS.MAXRUPEE
COM.QLOANS.PESA
MAGICALAPPZONE.INSTAPERSONALLOAN.LOANONLINE
LIST;
    /*     * 差的贷款类大写名* */
    const BAD_LOAN_APP_NAME_KEYWORDS = <<<LIST
QUIKKLOAN
OFFERMELOAN
LOANIT
LOANTIGER
ILOAN
ONLINEPERSONALLOAN
FASTCASH
ANYLOAN
RCMBUSINESS
SPEEDEECASH
CREDITHOUSE-INSTANTPERSONALCREDITLOANAPP
IZWELOANS
MYLOAN
ANTCASH
SNAPCASH
ONECASH
STARLOAN
KAMALOANPOCKET
CASHAAJ
PERSONALLOANS
BUSINESSWITHPAYTM
KHASHLOAN
ALLLOAN
IIFLLOANS
INSTANTPERSONALLOAN
MAXRUPEE
OIKOCREDITLOANS
LIST;

    /*     * 好的贷款类大写包名* */
    const GREAD_LOAN_APP_PKG_NAME_KEYWORDS = <<<LIST
COM.ARBAPPS.LOANEMICALC
COM.CASHCREDIT.CASH
COM.MARKET.LOANBUS
COM.FASTLOAN.CASHADVANCE.FINANCESERVICE.PROTABLEHELPER
IN.APPS.MYLOANCARE
IN.UPCASH.APP
COM.PAYTOTA.INSTANTLOAN.PHONEPE.LOAN.EMI
COM.LOANMART.APP
COM.CASHRUPEE.DOUBLECASH
COM.CAFT.RUPEEPLUS
COM.CASHJET.FINANCE
COM.CASHSAILING.SOLU
COM.CRYSTAL.ZOOMCASH
COM.INTERSTELLARZ.PERSONALLOANAPP
COM.LOAN.CASH
COM.LOANAPPLICATION.APP
COM.REGLOBE.CASHIFY
COM.LOANEASY
COM.TIANJI.CASHOUSE
COM.FLEXILOAN
COM.GORUPEE.GORUPEE
COM.TRANSFER.CASHOUT
COM.IMCBUSINESS.APP
LIST;

    protected $appLoanKeywords = [
        "loan",
        "rupee",
        "borrow",
    ];

    /* 互斥类型 */
    protected $appLoanMutual = [
        "lend" => "calendar",
    ];
    protected $nonFinanceKeywords = [
        "cash",
        "money",
        "cred",
        "kred"
    ];

    /**
     * 获取用户最后一个批次的App应用数据
     * @return array
     */
    public function getLastVersionApplication() {
        $data = Cache::remember($this->getCacheKey('getLastVersionApplication' . self::CACHE_EKEY, $this->user->id), self::CACHE_TIME, function () {
                    return (new UserApplication())->getLastVersionData($this->user->id);
                });
        return $data;
    }

    public function getAppRally(Order $order, $columnName = "app_name", $day = 0) {
        return (new UserApplication())->getAppRally($order, $columnName, $day)->get()->toArray();
        $data = Cache::remember($this->getCacheKey('user_application_app_rally_1' . self::CACHE_EKEY, $order->id, $columnName, $day), 1, function () use ($order, $columnName, $day) {
                    return (new UserApplication())->getAppRally($order, $columnName, $day)->get()->toArray();
                });
        return $data;
    }

    public function getAppListIndex(Order $order) {
        $userAppList = new UserApplication();
        $indexList = [
            "pack_cnt" => ["columnName" => "pkg_name", "days" => 0],
            "name_cnt" => ["columnName" => "app_name", "days" => 0],
            "loan_app1" => ["columnName" => "pkg_name", "days" => 0, "appList" => "LOAN_APP_PKG_NAME_KEYWORDS"],
            "loan_app2" => ["columnName" => "app_name", "days" => 0, "appList" => "LOAN_APP_NAME_KEYWORDS"],
            "install_l1d" => ["columnName" => "app_name", "days" => 1],
            "install_l1d_rate" => ["columnName" => "app_name", "days" => 1, 'deno' => 'name_cnt'],
            "install_l3d" => ["columnName" => "app_name", "days" => 3],
            "install_l3d_rate" => ["columnName" => "app_name", "days" => 3, 'deno' => 'name_cnt'],
            "install_l7d" => ["columnName" => "app_name", "days" => 7],
            "install_l7d_rate" => ["columnName" => "app_name", "days" => 7, 'deno' => 'name_cnt'],
            "install_loan_app1_l1d" => ["columnName" => "pkg_name", "days" => 1, "appList" => "LOAN_APP_PKG_NAME_KEYWORDS"],
            "install_loan_app1_l1d_lr" => ["columnName" => "pkg_name", "days" => 1, "appList" => "LOAN_APP_PKG_NAME_KEYWORDS", "deno" => "loan_app1"],
            "install_loan_app1_l1d_rate" => ["columnName" => "pkg_name", "days" => 1, "appList" => "LOAN_APP_PKG_NAME_KEYWORDS", "deno" => "name_cnt"],
            "install_loan_app1_l3d" => ["columnName" => "pkg_name", "days" => 3, "appList" => "LOAN_APP_PKG_NAME_KEYWORDS"],
            "install_loan_app1_l3d_lr" => ["columnName" => "pkg_name", "days" => 3, "appList" => "LOAN_APP_PKG_NAME_KEYWORDS", "deno" => "loan_app1"],
            "install_loan_app1_l3d_rate" => ["columnName" => "pkg_name", "days" => 3, "appList" => "LOAN_APP_PKG_NAME_KEYWORDS", "deno" => "name_cnt"],
            "install_loan_app1_l7d" => ["columnName" => "pkg_name", "days" => 7, "appList" => "LOAN_APP_PKG_NAME_KEYWORDS"],
            "install_loan_app1_l7d_lr" => ["columnName" => "pkg_name", "days" => 7, "appList" => "LOAN_APP_PKG_NAME_KEYWORDS", "deno" => "loan_app1"],
            "install_loan_app1_l7d_rate" => ["columnName" => "pkg_name", "days" => 7, "appList" => "LOAN_APP_PKG_NAME_KEYWORDS", "deno" => "name_cnt"],
            "install_loan_app2_l1d" => ["columnName" => "app_name", "days" => 1, "appList" => "LOAN_APP_NAME_KEYWORDS"],
            "install_loan_app2_l3d" => ["columnName" => "app_name", "days" => 3, "appList" => "LOAN_APP_NAME_KEYWORDS"],
            "install_loan_app2_l7d" => ["columnName" => "app_name", "days" => 7, "appList" => "LOAN_APP_NAME_KEYWORDS"],
            "new_bad_loan1" => ["columnName" => "pkg_name", "days" => 0, "appList" => "BAD_LOAN_APP_PKG_NAME_KEYWORDS"],
            "new_bad_loan1_loanrate" => ["columnName" => "pkg_name", "days" => 0, "appList" => "BAD_LOAN_APP_PKG_NAME_KEYWORDS", "deno" => "loan_app1"],
            "new_bad_loan1_rate" => ["columnName" => "pkg_name", "days" => 0, "appList" => "BAD_LOAN_APP_PKG_NAME_KEYWORDS", "deno" => "pack_cnt"],
            "new_bad_loan2" => ["columnName" => "app_name", "days" => 0, "appList" => "BAD_LOAN_APP_NAME_KEYWORDS"],
            "new_bad_loan2_loanrate" => ["columnName" => "app_name", "days" => 0, "appList" => "BAD_LOAN_APP_NAME_KEYWORDS", "deno" => "loan_app2"],
            "new_good_loan1" => ["columnName" => "pkg_name", "days" => 0, "appList" => "GREAD_LOAN_APP_PKG_NAME_KEYWORDS"],
            "new_good_loan1_loanrate" => ["columnName" => "pkg_name", "days" => 0, "appList" => "GREAD_LOAN_APP_PKG_NAME_KEYWORDS", "deno" => "loan_app1"]
        ];
        $res = [];
        foreach ($indexList as $key => $value) {
            if ($value) {
                if (isset($value['appList'])) {
                    $list = $this->getAppRally($order, $value['columnName'], $value['days']);
                    $appList = constant("self::" . $value['appList']);
                    $res[$key] = count($this->hasKeywordList(explode("\n", $appList), array_column($list, $value['columnName'])));
                } else {
                    $res[$key] = $userAppList->getAppRallyCount($order, $value['columnName'], $value['days']);
                }
                if (isset($value['deno'])) {
                    if ($res[$value['deno']]) {
                        $res[$key] = $res[$key] ? round($res[$key] / $res[$value['deno']] * 100, 2) : 0;
                    } else {
                        $res[$key] = $res[$key] > 0 ? 100 : 0;
                    }
                }
            }
        }
        foreach ($res as $k => $v) {
            $insertData = [
                "user_id" => $order->user_id,
                "order_id" => $order->id,
                "index_name" => $k,
                "index_value" => $v,
            ];
            $relateData = [
                "user_id" => $order->user_id,
                "order_id" => $order->id,
                "index_name" => $k,
            ];
            RiskDataIndex::updateOrCreate($relateData, $insertData);
        }
        return $res;
    }

    protected function getCacheKey(...$keys) {
        array_unshift($keys, __CLASS__);

        if ($merchantId = MerchantHelper::getMerchantId()) {
            array_unshift($keys, $merchantId);
        }

        return implode(':', $keys);
    }

    public function riskIndex20200906(Order $order) {
        $indexList = [
            "pack_cnt2" => $this->getPackCnt($order),
            "last_pack_cnt" => $this->getLastPackCnt($order),
            "keep_loan_pack_cnt" => $this->getKeepLoanPackCnt($order),
            "keep_loan_pack_rate" => $this->getKeepLoanPackRate($order),
            "last_install_loan_app1_b30d" => $this->getLastInstallLoanApp1B30d($order),
        ];
        if ($indexList['last_pack_cnt'] && $indexList['last_install_loan_app1_b30d']) {
            $indexList['last_install_loan_app1_b30d_rate'] = $indexList['last_install_loan_app1_b30d'] / $indexList['last_pack_cnt'];
        } else {
            $indexList['last_install_loan_app1_b30d_rate'] = 0;
        }

        return $indexList;
    }

    public function getLastPackCnt(Order $order) {
        $data = Cache::remember($this->getCacheKey('getLastPackCnt' . self::CACHE_EKEY, $order->user_id), self::CACHE_TIME, function () use($order) {
                    return count((new UserApplication())->getLastVersionData($order->user_id, $order->created_at));
                });
        return $data;
    }

    public function getLastInstallLoanApp1B30d(Order $order) {
        $data = Cache::remember($this->getCacheKey('getLastInstallLoanApp1B30d' . self::CACHE_EKEY, $order->user_id), self::CACHE_TIME, function () use ($order) {
                    $appList = (new UserApplication())->getLastVersionData($order->user_id, $order->created_at, 30);
                    $loanAppCnt = 0;
                    foreach ($appList as $item) {
                        $pkgName = strtolower($item['pkg_name']);
                        $appName = strtolower($item['app_name']);
                        if ($this->checkLoanType($appName, $pkgName)) {
                            $loanAppCnt++;
                        }
                    }
                    return $loanAppCnt;
                });
        return $data;
    }

    protected function hasKeywordList($keywordList, array $appList) {
        $hasBlackKeywordList = [];
        foreach ($appList as $item) {
            $appName = $this->simplifyName($item);
            foreach ($keywordList as $blackKeyword) {
                $blackKeyword = $this->simplifyName($blackKeyword);
                if (strpos($appName, $blackKeyword) !== false) {
                    if (!in_array($item, $hasBlackKeywordList)) {
                        $hasBlackKeywordList[] = $item;
                    }
                }
            }
        }
//        print_r($hasBlackKeywordList);exit;
        return $hasBlackKeywordList;
    }

    protected function simplifyName($name) {
        return strtolower(preg_replace('# #', '', $name));
    }

    public function getKeepPackCnt(Order $order) {
        
    }

    public function getKeepPack(Order $order, $loanType = false) {
        $data = Cache::remember($this->getCacheKey('getKeepPack' . self::CACHE_EKEY, $order->user_id, $loanType), self::CACHE_TIME, function() use ($order, $loanType) {
                    $minVersion = UserApplication::model()->getMinVersion($order->user_id);
                    $maxVersion = UserApplication::model()->getLastVersion($order->user_id, $order->created_at);
                    $minVersionList = UserApplication::model()->getAppPackAndNameList($order, $minVersion);
                    $lastVersionList = UserApplication::model()->getAppPackAndNameList($order, $maxVersion);
//                    echo "userId:" . $order->user_id . PHP_EOL .
//                    "minV:" . $minVersion . PHP_EOL .
//                    "maxV:" . $maxVersion . PHP_EOL .
//                    "create" . $order->created_at . PHP_EOL .
//                    "minCount" . count($minVersionList) . PHP_EOL .
//                    "maxCount" . count($lastVersionList);
                    $res = [];
                    #贷款类APP
                    if ($loanType) {
                        $mergedApp = array_keys(array_intersect_key($minVersionList, $lastVersionList));
                        $res = 0;
                        foreach ($mergedApp as $nameAndPkg) {
                            $pkgName = strtolower($lastVersionList[$nameAndPkg]['pkg_name']);
                            $appName = strtolower($lastVersionList[$nameAndPkg]['app_name']);
                            if ($this->checkLoanType($appName, $pkgName)) {
                                $res++;
//                                echo $res . PHP_EOL;
//                                print_r($lastVersionList[$nameAndPkg]);
                            }
                        }
                        return $res;
                    } else {

                        $mergedApp = array_merge(array_keys($minVersionList), array_keys($lastVersionList));
                        foreach ($mergedApp as $k => $v) {
                            $res[$v] = 1;
                        }
                        return count(array_keys($res));
                    }
                });
        return $data;
    }

    public function checkLoanType($appName, $pkgName) {
        if ($this->hitKeywords($pkgName, $this->appLoanKeywords)) {
//            echo "A";
            return true;
        }
        if ($this->hitMutualKeywords($pkgName, $this->appLoanMutual)) {
//            echo "B";
            return true;
        }
        if ($this->hitKeywords($appName, $this->appLoanKeywords)) {
//            echo "C";
            return true;
        }
        if ($this->hitMutualKeywords($appName, $this->appLoanMutual)) {
//            echo "D";
            return true;
        }
        $appType = $this->hitAppType($appName);
        if ($appType == 'loan') {
            return true;
        }
        if ("finance" != $appType) {
            if ($this->hitKeywords($pkgName, $this->nonFinanceKeywords)) {
//                echo "E";
                return true;
            }
            if ($this->hitKeywords($appName, $this->nonFinanceKeywords)) {
//                echo "F";
                return true;
            }
        }
        return false;
    }

    public function getKeepLoanPackCnt(Order $order) {
        return $this->getKeepPack($order, true);
    }

    public function getPackCnt(Order $order) {
        return $this->getKeepPack($order);
    }

    public function getKeepLoanPackRate(Order $order) {
        $keepLoanPackCnt = $this->getKeepLoanPackCnt($order);
        if (!$keepLoanPackCnt) {
            return 0;
        }
        $packCnt = $this->getPackCnt($order);
        if (!$packCnt) {
            return 1;
        }
        return $keepLoanPackCnt / $packCnt;
    }

    public function hitKeywords($appName, $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($appName, $keyword) !== false && (strpos($appName, 'calc') === false && strpos($appName, 'pfbal') === false)) {
                return true;
            }
        }
        return false;
    }

    public function hitMutualKeywords($appName, $keywords) {
        foreach ($keywords as $keyword => $mutual) {
            if (strpos($appName, $keyword) !== false) {
                if (strpos($appName, $mutual) === false && (strpos($appName, 'calc') === false && strpos($appName, 'pfbal') === false)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function hitAppType($appName) {
        foreach (UserApplication::APP_TYPE as $item) {
            if (strtolower($item[0]) == $appName || strtolower($item[1]) == $appName) {
                return $item[2];
            }
        }
        return "";
    }

}
