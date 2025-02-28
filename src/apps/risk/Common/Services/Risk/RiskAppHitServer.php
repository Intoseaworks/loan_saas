<?php

namespace Risk\Common\Services\Risk;

use Common\Services\BaseService;
use Common\Utils\Data\StringHelper;

class RiskAppHitServer extends BaseService
{
    /**
     * 安装量top应用列表
     * @var array
     */
    const TOP_APP_LIST = [
        'LazyPay',
        'RupeeMax',
        'CashMama',
        'FlexSalary',
        'Erupee',
        'CashIn',
        'Mi Credit',
        'Atome Credit',
        'EarlySalary',
        'KreditBee',
        'LoanFront',
        'CashBean',
        'StashFin',
        'Walnut',
        'Kreditzy',
        'Mudrakwik',
        'Loans',
        'SmartCoin',
        'LightingLoan',
        'FlashCash',
        'CASHe',
        'Avail',
        'TVS Credit Saathi',
        'MoneyTap',
        'PhoneParLoan',
        'Shubh Loans',
        'UPWARDS',
        'MoneyView',
        '10MinuteLoan',
        'PaySense',
        'Yaarii',
        'NIRA',
        'Citrus',
        'ScanID',
        'शुभ लोन्स',
        'శుభ్ లోన్స్',
        'ScanID',
    ];
    /** @var string 通讯录姓名比对app名称list。由于文档提供为文本形式，此处也直接写字符串格式，解析使用。方便更新 */
    const CONTACT_NAME_COMPARISON_APP_LIST = <<<LIST
OkCash
LazyPay
Robocash
Flexi Money
RupeeMax
CashMama
OYE
FlexSalary
LoanU
HiCash
uuCash
CashBus
Rupeebus
iCredit
Erupee
LoanBro
CrazyRupee
Cash Max
CashIn
Creditt
Ocash
RupeeStar
Cash Bowl
happymonies
Rupee Redee
Mi Credit
Atome Credit
RupeePlus
Credime
UCash
GotoCash
WifiCash
EarlySalary
Loan Agent
KreditBee
LoanFront
CashBean
RupeeLoan
CashUp
Faircent
ATL
Rupee Plus
MoreRupee
StashFin
RupeeCash
LendKaro
Loanflix
Walnut
SpeedCash
MoneyBox
Kreditzy
BillionCash
Mudrakwik
ABCash
CashPapa
Cashcred
Credicxo
Golden Lightning
KreditOne
Z2P
Bettr Credit
Mystro
Cash365
MoneyWow
iRupee
Loans
SmartCoin
LightingLoan
NanoCred
FlashCash
Udhaar-E
MoneyLoji
Cash+
CASHe
PayMeIndia
RapidRupee
InstaMoney
ATD Money
CashTap
LoanIt
Rupeeclub
Loan Dost
SUDHA POCKET
Salary Dost
Rupeelend
RupeeCircle
Avail
SimplyCash
TVS Credit Saathi
MoneyTap
PhoneParLoan
Phocket
Shubh Loans
MoneyMore
UPWARDS
Salary Now
Quikkloan
Instasalary
Finacri
MoneyView
instaCash7
10MinuteLoan
Checkpoint
PaySense
Easy Loans
AbhiPaisa
Yaarii
Money in Minutes
EzeCol Pro
LoanMart
NIRA
LoanSimple Team
vCard
Myruna
Cashkumar
Credy
VCredit
Instant Mudra
CashTm
MoneyEnjoy
CashPie
Manappuram Personal Loan
MadRupee
MONEXO
Get Money
Instant Loan
VServe Partner
Rapid Rupee
Balikbayad
आसान ऋण
ScanID
OYE!
CashClub
Citrus
रैपिडरुपया
Cash Bar
iEasyLoan
CashInyou
शुभ लोन्स
Fast Cash
Fast Rupee
WeRupee
శుభ్ లోన్స్
Cash Credit
LeCred
DhanaDhan
Modiloan
भाषा
GetRupee
CashPanda
CashKey
QuickCash
My Loan
ஷுப் லோன்ஸ்
ಶುಭ ಲೋನ್ಸ್
शुभ लोन
மொழி
LIST;

    /** @var string 全量高危app清单 */
    const HIGH_RISK_APP_LIST = <<<LIST
OkCash
LazyPay
Robocash
RupeeMax
CashMama
OYE
FlexSalary
HiCash
uuCash
CashBus
Rupeebus
iCredit
Erupee
LoanBro
CrazyRupee
Cash Max
CashIn
Creditt
Ocash
Cash Bowl
happymonies
Rupee Redee
Mi Credit
Atome Credit
RupeePlus
Credime
UCash
GotoCash
WifiCash
EarlySalary
Loan Agent
KreditBee
LoanFront
CashBean
RupeeLoan
CashUp
Faircent
ATL
MoreRupee
StashFin
RupeeCash
LendKaro
Loanflix
Walnut
SpeedCash
MoneyBox
Kreditzy
Mudrakwik
CashPapa
Credicxo
Golden Lightning
KreditOne
Z2P
Bettr Credit
Mystro
Cash365
MoneyWow
iRupee
Loans
SmartCoin
LightingLoan
NanoCred
FlashCash
My Loan
MoneyLoji
Cash+
CASHe
PayMeIndia
RapidRupee
InstaMoney
ATD Money
CashTap
LoanIt
Rupeeclub
Loan Dost
SUDHA POCKET
Salary Dost
Rupeelend
RupeeCircle
Avail
SimplyCash
TVS Credit Saathi
MoneyTap
PhoneParLoan
Phocket
Shubh Loans
MoneyMore
UPWARDS
Salary Now
Quikkloan
Instasalary
Finacri
MoneyView
instaCash7
10MinuteLoan
Checkpoint
PaySense
Easy Loans
AbhiPaisa
Yaarii
Money in Minutes
EzeCol Pro
LoanMart
NIRA
LoanSimple Team
vCard
Myruna
Cashkumar
Credy
VCredit
Instant Mudra
CashTm
MoneyEnjoy
CashPie
Manappuram Personal Loan
MadRupee
MONEXO
Get Money
VServe Partner
Rapid Rupee
आसान ऋण
ScanID
OYE!
CashClub
Citrus
रैपिडरुपया
Cash Bar
iEasyLoan
CashInyou
शुभ लोन्स
Fast Cash
Fast Rupee
WeRupee
శుభ్ లోన్స్
Cash Credit
भाषा
ஷுப் லோன்ஸ்
ಶುಭ ಲೋನ್ಸ್
शुभ लोन
மொழி
com.dcfintech.golden.MyApplication
LIST;

    /**
     * App黑名单，之后优化到数据库
     * @var array
     */
    protected $appBlackList = [
        'Early Salary',
        'Money View',
        'CASHe',
        'Money in Minutes',
        'Flex salary',
        'Pay Sense',
        'Rupee lend',
        'dhani',
        'indialends',
        'nira',
        'Capital First Limited',
        'mi credit',
        'zestmoney',
        'cashe for salaried',
        'qbera',
        'redcarpet credit',
        'loanwalle',
        'slice',
        'oye loans',
        'mpokket for student',
        'avail finance',
        'robocash india',
        'payme india',
        'mudrakwik',
        'rupeecircle',
        'phocket',
    ];
    /**
     * App名称关键字黑名单
     * @var array
     */
    protected $appBlackKeyword = [
        'cash',
        'loan',
        'rupee',
        'bus'
    ];

    public function hitLoanAppCount(array $appList)
    {
        $intersect = $this->intersectBlackAppList($appList);

        $hasBlackKeywordList = $this->hasBlackKeywordList($appList);

        // 由于上面方法返回的为applist数组原值，可直接进行去重处理
        $res = array_unique(array_merge($intersect, $hasBlackKeywordList));

        return $res;
    }

    /**
     * 命中app黑名单列表
     * 返回数组统一填充原数组值
     * @param array $appList
     * @return array
     */
    public function intersectBlackAppList(array $appList)
    {
        return $this->intersect($appList, $this->appBlackList);
    }

    /**
     * 两app列表交集比较
     * 返回数组统一填充第一个列表原数组值
     * @param array $appList
     * @param array $appList2
     * @return array
     */
    public function intersect(array $appList, array $appList2)
    {
        $appList2Res = [];
        foreach ($appList2 as $item) {
            $app2Item = $this->simplifyName($item);
            $appList2Res[] = $app2Item;
        }

        $hit = [];
        foreach ($appList as $item) {
            $appName = $this->simplifyName($item);
            if (in_array($appName, $appList2Res)) {
                $hit[] = $item;
            }
        }

        return array_unique($hit);
    }

    protected function simplifyName($name)
    {
        return strtolower(preg_replace('# #', '', $name));
    }

    /**
     * app列表中含有指定关键字的列表
     * 返回数组统一填充原数组值
     * @param array $appList
     * @return array
     */
    protected function hasBlackKeywordList(array $appList)
    {
        $hasBlackKeywordList = [];
        foreach ($appList as $item) {
            $appName = $this->simplifyName($item);
            foreach ($this->appBlackKeyword as $blackKeyword) {
                $blackKeyword = $this->simplifyName($blackKeyword);
                if (strpos($appName, $blackKeyword) !== false) {
                    $hasBlackKeywordList[] = $item;
                }
            }
        }
        return $hasBlackKeywordList;
    }

    public function inAppBlackList($appName)
    {
        $appBlackList = [];
        foreach ($this->appBlackList as $item) {
            $appBlackList[] = $this->simplifyName($item);
        }
        $appName = $this->simplifyName($appName);

        return in_array($appName, $appBlackList);
    }

    public function comparisonContactNameList($arr)
    {
        $appList = $this->getContactNameComparisonAppList();
        $appList = StringHelper::clearSpaceToLower($appList);

        $arr = StringHelper::clearSpaceToLower($arr);

        if (is_array($arr)) {
            return array_unique(array_intersect($arr, $appList));
        }
        return in_array($arr, $appList);
    }

    public function getContactNameComparisonAppList()
    {
        $listStr = self::CONTACT_NAME_COMPARISON_APP_LIST;

        $listArr = explode(PHP_EOL, $listStr);

        return $listArr;
    }

    public function comparisonContactNameList2(string $str)
    {
        $appList = $this->getContactNameComparisonAppList();
        $formatAppList = [];
        foreach ($appList as $item) {
            $key = StringHelper::clearSpaceToLower($item);
            $formatAppList[$key] = $item;
        }

        $str = StringHelper::clearSpaceToLower($str);

        return array_get($formatAppList, $str);
    }

    public function comparisonHighRiskAppList($arr)
    {
        $appList = $this->getHighRiskAppList();
        $appList = StringHelper::clearSpaceToLower($appList);

        $arr = StringHelper::clearSpaceToLower($arr);

        if (is_array($arr)) {
            return array_unique(array_intersect($arr, $appList));
        }
        return in_array($arr, $appList);
    }

    public function getHighRiskAppList()
    {
        $listStr = self::HIGH_RISK_APP_LIST;

        return explode(PHP_EOL, $listStr);
    }
}
