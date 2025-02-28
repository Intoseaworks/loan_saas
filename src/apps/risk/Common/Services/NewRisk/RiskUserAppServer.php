<?php

namespace Risk\Common\Services\NewRisk;

//use Risk\Common\Models\Business\UserData\UserApplication;
use Common\Utils\Data\StringHelper;
use Illuminate\Support\Str;
use Risk\Common\Models\UserData\UserApp as UserApplication;
use Risk\Common\Models\Business\Order\Order;
use Illuminate\Support\Facades\Cache;
use Common\Services\BaseService;
use Common\Utils\MerchantHelper;
use Risk\Common\Models\Business\RiskData\RiskDataIndex;
use Risk\Common\Models\UserData\VarParametersSystemApp;

class RiskUserAppServer extends BaseService {

    const SYSTEM_APP_TYPE = 'adj_system';
    const LOAN_APP_TYPE = 'loan';
    const GLOAN_APP_TYPE = 'gloan';
    const PRM_APP_TYPE = 'prm';
    const BTM_APP_TYPE = 'btm_app';
    const UP_APP_TYPE = 'up';
    const SB_APP_TYPE = 'sb';
    const SHOP_APP_TYPE = 'shop';
    const BANK_APP_TYPE = 'bank';
    const PAY_APP_TYPE = 'pay';
    const GOOD_APP_TYPE = 'good';
    const BAD_APP_TYPE = 'bad_app';
    const NEW_GOOD_APP_TYPE = 'new_good_app';
    const REMARK_PKG_NAME_SMALL = 'app包名转小写后，匹配';
    const REMARK_PKG_NAME = 'app包名匹配';
    const REMARK_PKG_NAME_NOT_IN = 'app包名不在以下列表中';
    const REMARK_APP_NAME = 'app名称转小写，并且去除字符串的空格后，进行匹配';
    const CACHE_TIME = 1;
    const CACHE_EKEY = "C"; //扩展key 修改后清空缓存
    /*     * 系统类app小写包名后两位* */
    const SYS_APP_PKG_NAME_KEYWORDS = ['com.android',
        'com.samsung',
        'com.lge',
        'com.sec',
        'com.sonymobile',
        'com.huawei',
        'com.evenwell',
        'com.google',
        'com.mediatek',
        'com.vivo',
        'com.asus',
        'com.motorola',
        'com.oppo',
        'com.htc',
        'com.cootek',
        'com.qualcomm',
        'com.sonyericsson',
        'jp.co',
        'com.coloros',
        'com.oneplus',
        'com.miui',
        'com.lenovo',
        'com.transsion',
        'android.autoinstalls',
        'com.letv',
        'com.meizu',
        'com.zui',
        'com.tct',
        'com.fujitsu',
        'com.tplink',
        'com.qiku',
        'com.xiaomi',
        'com.tcl',
        'com.fihtdc',
        'com.sprd',
        'com.nttdocomo',
        'com.huaqin',
        'plugin.sprd',
        'com.hmdglobal',
        'com.kddi',
        'com.example',
        'com.jrdcom',
        'com.zte',
        'jp.softbank',
        'com.ape',
        'com.gome',
        'com.qti',
        'com.bbk',
        'com.heytap',
        'com.gionee',
        'com.monotype',
        'com.lguplus',
        'com.lava',
        'com.mi',
        'com.skt',
        'gn.camera',
        'com.nearme',
        'com.itel',
        'com.nbc',
        'com.atok',
        'com.blackshark',
        'com.tinno',
        'org.codeaurora',
        'com.baidu',
        'com.verizon',
        'com.wingtech',
        'com.hy',
        'com.gameloft',
        'ph.com',
        'com.sony',
        'net.oneplus',
        'com.pri',
        'com.dti',
        'com.oplus',
        'com.amazon',
        'com.hancom',
        'com.arcsoft',
        'com.kt',
        'com.sprint',
        'com.lmkj',
        'com.sec',
        'com.monotype',
        'com.android',
        'com.samsung',
        'com.lge',
        'com.huawei',
        'com.cootek',
        'com.sonymobile',
        'china.samsung',
        'com.google',
        'com.htc',
        'com.evenwell',
        'com.sonyericsson',
        'com.lenovo',
        'com.motorola',
        'com.oppo',
        'com.mediatek',
        'com.asus',
        'com.vivo',
        'com.qualcomm',
        'com.coloros',
        'com.miui',
        'com.acer',
        'com.meizu',
        'com.tcl',
        'com.zte',
        'com.xiaomi',
        'com.blackberry',
        'com.mi',
        'com.qti',
        'com.bbk',
        'cn.nubia',
        'com.nearme'];

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

    public function __construct() {
        $this->systemapps = VarParametersSystemApp::model()->where('APP_type', self::SYSTEM_APP_TYPE)->pluck('pkg_name')->toArray();
        $this->loanapps_app_name = VarParametersSystemApp::model()->where('APP_type', self::LOAN_APP_TYPE)
            ->where('remark', self::REMARK_APP_NAME)->pluck('pkg_name')->toArray();
        $this->loanapps_pkg_name_not = VarParametersSystemApp::model()->where('APP_type', self::LOAN_APP_TYPE)
            ->where('remark', self::REMARK_PKG_NAME_NOT_IN)->pluck('pkg_name')->toArray();
        $this->gloanapps_app_name = VarParametersSystemApp::model()->where('APP_type', self::GLOAN_APP_TYPE)
            ->where('remark', self::REMARK_APP_NAME)->pluck('pkg_name')->toArray();
        $this->gloanapps_pkg_name_small = VarParametersSystemApp::model()->where('APP_type', self::GLOAN_APP_TYPE)
            ->where('remark', self::REMARK_PKG_NAME_SMALL)->pluck('pkg_name')->toArray();
        $this->prmapps = VarParametersSystemApp::model()->where('APP_type', self::PRM_APP_TYPE)->pluck('pkg_name')->toArray();
        $this->upapps = VarParametersSystemApp::model()->where('APP_type', self::UP_APP_TYPE)->pluck('pkg_name')->toArray();
        $this->sbapps = VarParametersSystemApp::model()->where('APP_type', self::SB_APP_TYPE)->pluck('pkg_name')->toArray();
        $this->shopapps = VarParametersSystemApp::model()->where('APP_type', self::SHOP_APP_TYPE)->pluck('pkg_name')->toArray();
        $this->btmapps = VarParametersSystemApp::model()->where('APP_type', self::BTM_APP_TYPE)->pluck('pkg_name')->toArray();
        $this->bankapps_app_name = VarParametersSystemApp::model()->where('APP_type', self::BANK_APP_TYPE)
            ->where('remark', self::REMARK_APP_NAME)
            ->pluck('pkg_name')->toArray();
        $this->bankapps_pkg_name_small = VarParametersSystemApp::model()->where('APP_type', self::BANK_APP_TYPE)
            ->where('remark', self::REMARK_PKG_NAME_SMALL)
            ->pluck('pkg_name')->toArray();
        $this->payapps = VarParametersSystemApp::model()->where('APP_type', self::PAY_APP_TYPE)->pluck('pkg_name')->toArray();
        $this->goodapps = VarParametersSystemApp::model()->where('APP_type', self::GOOD_APP_TYPE)->pluck('pkg_name')->toArray();
        $this->badapps = VarParametersSystemApp::model()->where('APP_type', self::BAD_APP_TYPE)->pluck('pkg_name')->toArray();
        $this->newgoodapps = VarParametersSystemApp::model()->where('APP_type', self::NEW_GOOD_APP_TYPE)->pluck('pkg_name')->toArray();
    }

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
        $data = Cache::remember($this->getCacheKey('getLastPackCnt' . self::CACHE_EKEY, $order->user_id), self::CACHE_TIME, function () use ($order) {
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
        $data = Cache::remember($this->getCacheKey('getKeepPack' . self::CACHE_EKEY, $order->user_id, $loanType), self::CACHE_TIME, function () use ($order, $loanType) {
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

    public function isSysType($userapp) {
        if ($userapp->is_system) {
//            echo "A";
            return true;
        }
        $packString = explode('.', $userapp->small_pkg_name);
        if ( (isset($packString[1]) && $this->hitKeywords($packString[0] . '.' . $packString[1], self::SYS_APP_PKG_NAME_KEYWORDS)) ||
                $this->hitKeywords($userapp->pkg_name, $this->systemapps)) {
//            echo "C";
            return true;
        }
        return false;
    }

    public function isLoanType($userapp) {
        if ( $userapp->pkg_name == 'ph.moneycat' ) {
//            echo "C";
            return true;
        }
        if ( $userapp->small_app_name == 'gcash' ) {
//            echo "C";
            return false;
        }
        if ( $this->isBankType($userapp) || $this->isPayType($userapp) || Str::contains($userapp->pkg_name,'earn') || $this->hitKeywords($userapp->pkg_name,$this->loanapps_pkg_name_not) ) {
//            echo "C";
            return false;
        }

        if ( Str::contains($userapp->small_app_name,['cash','peso','utang','pera','loan','credit','kredit','lend','borrow','tala']) ||
            Str::contains($userapp->pkg_name,['loan','cash','peso','lend','pera']) || $this->hitKeywords($userapp->small_app_name,$this->loanapps_app_name)
        ) {
//            echo "C";
            return true;
        }
        return false;
    }

    public function isGloanType($userapp) {
        if ( $this->hitKeywords($userapp->small_app_name, $this->gloanapps_app_name) || $this->hitKeywords($userapp->small_pkg_name, $this->gloanapps_pkg_name_small) ) {
//            echo "C";
            return true;
        }
        return false;
    }

    public function isPrmType($userapp) {
        if ($this->hitKeywords($userapp->small_pkg_name, $this->prmapps)) {
//            echo "C";
            return true;
        }
        return false;
    }

    public function isBtmType($userapp) {
        if ($this->hitKeywords($userapp->small_pkg_name, $this->btmapps)) {
            return true;
        }
        return false;
    }

    public function upAppCount($userapp, $initData) {
        $initData['upAppCount'] = $initData['upAppCount'] ?? 0;
        if ($this->hitKeywords($userapp->small_pkg_name, $this->upapps)) {
            $initData['userApplicationDiffCreateInstall']->is_up = 1;
            $initData['upAppCount']++;
        }
        return $initData;
    }

    public function sbAppCount($userapp, $initData) {
        $initData['sbAppCount'] = $initData['sbAppCount'] ?? 0;
        if ($this->hitKeywords($userapp->small_pkg_name, $this->sbapps)) {
            $initData['userApplicationDiffCreateInstall']->is_sb = 1;
            $initData['sbAppCount']++;
        }
        return $initData;
    }

    public function shopAppCount($userapp, $initData) {
        $initData['shopAppCount'] = $initData['shopAppCount'] ?? 0;
        if ($this->hitKeywords($userapp->small_pkg_name, $this->shopapps)) {
            $initData['userApplicationDiffCreateInstall']->is_shop = 1;
            if ( !$initData['is_adj_system'] ){
                $initData['userApplicationDiffCreateInstall']->is_nsys_shop = 1;
            }
            $initData['shopAppCount']++;
        }
        return $initData;
    }

    public function notSystemappCountInMonth($is_adj_system, $dif_create_install, $initData) {
        $initData['notSystemappCountInMonth'] = $initData['notSystemappCountInMonth'] ?? 0;
        if ($is_adj_system == 0 && $dif_create_install <= 30) {
            $initData['notSystemappCountInMonth']++;
        }
        return $initData;
    }

    public function shopAppOverMonthCount($userapp, $is_adj_system, $dif_create_install, $initData) {
        $initData['shopAppOverMonthCount'] = $initData['shopAppOverMonthCount'] ?? 0;
        if ($is_adj_system == 0 && $dif_create_install > 30 && $this->hitKeywords($userapp->pkg_name, $this->shopapps)) {
            $initData['shopAppOverMonthCount']++;
        }
        return $initData;
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

    public function hitKeywords($appName, $keywordsArr) {
        return in_array($appName, $keywordsArr);
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

    public function isBankType($userapp) {
        if ( $this->hitKeywords($userapp->small_pkg_name, $this->bankapps_pkg_name_small) ||  $this->hitKeywords($userapp->small_app_name, $this->bankapps_app_name)) {
//            echo "C";
            return true;
        }
        return false;
    }

    public function isGoodType($userapp) {
        if ($this->hitKeywords($userapp->small_pkg_name, $this->goodapps)) {
//            echo "C";
            return true;
        }
        return false;
    }

    public function isPayType($userapp) {
        if ( $this->hitKeywords($userapp->pkg_name, $this->payapps) || Str::contains($userapp->small_app_name,['pay','gcash','tonik']) ) {
//            echo "C";
            return true;
        }
        return false;
    }

    public function isBadType($userapp) {
        if ($this->hitKeywords($userapp->small_pkg_name, $this->badapps)) {
//            echo "C";
            return true;
        }
        return false;
    }

    public function isNewGoodType($userapp) {
        if ($this->hitKeywords($userapp->small_pkg_name, $this->newgoodapps)) {
//            echo "C";
            return true;
        }
        return false;
    }
}
