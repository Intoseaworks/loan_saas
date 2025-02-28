<?php
/**
 * Created by PhpStorm.
 * User: Ace
 * Date: 2020-12-22
 * Time: 20:33
 */

namespace Approve\Admin\Services\Rule;


use Admin\Services\BaseService;
use Api\Services\User\UserBlackServer;
use Carbon\Carbon;
use Common\Models\Order\Order;
use Common\Models\Risk\RiskBlacklist;
use Common\Services\Order\OrderServer;
use Common\Services\Risk\RiskBlacklistServer;

class ApproveRuleService extends BaseService
{
    const OPERATION_GREY_LIST_7DAYS = '加入申请灰名单-7天';
    const OPERATION_GREY_LIST_30DAYS = '加入申请灰名单-30天';
    const OPERATION_GREY_LIST_180DAYS = '加入申请灰名单-180天';
    const OPERATION_GREY_LIST_FOREVER = '加入申请灰名单-永久';
    const OPERATION_REJECT = '拒绝';
    const OPERATION_CANCEL = '取消';

    /*************************************************************************************************************
     * Rule start
     ************************************************************************************************************/

    /** 初审模块 */
    /** 填写证件号错误 加入申请灰名单 */
    const R03WD = 'R03WD';
    /** 非有效头像 第一次：待补件 第二次：加入申请灰名单-30天 */
    const R01OHPIV = 'R01OHPIV';
    /** 非有效证件 第一次：待补件 第二次：加入申请灰名单-30天 */
    const R03OCPIV = 'R03OCPIV';
    /** 证件不符合标准 第一次：待补件 第二次：拒绝 */
    const R03OCPUQ = 'R03OCPUQ';
    /** 头像不符合标准 第一次：待补件 第二次：拒绝 */
    const R03OHPUQ = 'R03OHPUQ';
    /** 身份信息伪造 加入申请灰名单-永久 风控黑名单 */
    const R01OCPFF = 'R01OCPFF';
    /** 证件影像存在风险 第一次：待补件 第二次：加入申请灰名单-180天 */
    const R01OCPSP = 'R01OCPSP';
    /** 头像与证照不一致 第一次：待补件 第二次：加入申请灰名单-30天 */
    const R01OHPDF = 'R01OHPDF';
    /** 非菲律宾公民 加入申请灰名单-永久 风控黑名单 */
    const R03DL = 'R03DL';
    /** 非菲律宾公民 加入申请灰名单-180天 */
    const R03OAGUQ = 'R03OAGUQ';
    /** 工作证已经过期 加入申请灰名单-30天 */
    const R03OWCCD = 'R03OWCCD';
    /** 客户证件姓名与在线申请不符 第一次：待补件 第二次：加入申请灰名单-30天 */
    const R03OWCDF2 = 'R03OWCDF2';
    /** 头像和工作证显示不同的人 第一次：待补件 第二次：加入申请灰名单-30天 */
    const R03OWCDF3 = 'R03OWCDF3';
    /** 工作证无效 第一次：待补件 第二次：拒绝 */
    const R03OWCIV = 'R03OWCIV';
    /** 工作证的真实性不确定 第一次：待补件 第二次：拒绝 */
    const R03OWCNI = 'R03OWCNI';
    /** 工作证不符合申请标准 第一次：待补件 第二次：拒绝 */
    const R03OWCUQ = 'R03OWCUQ';
    /** 身份证过期 第一次：待补件 第二次：拒绝 */
    const R03SG = 'R03SG';
    /** 行业或公司的负面信息 加入申请灰名单-30天 */
    const R01FH = 'R01FH';

    /** 电审模块 */
    /** 申请人重复申请 加入申请灰名单-30天 */
    const R03OIDOL = 'R03OIDOL';
    /** 职业不符合规定 加入申请灰名单-180天 */
    const R03OJIUQ = 'R03OJIUQ';
    /** 风险申请人重复申请  加入申请灰名单-180天*/
    const R01OIDOL = 'R01OIDOL';
    /** 取消 取消申请 */
    const R02QB = 'R02QB';
    /** 负面信息 加入申请灰名单-永久 */
    const R02OHBNI = 'R02OHBNI';
    /** 负面信息-短信异常 加入申请灰名单 */
    const R01OMGNI = 'R01OMGNI';
    /** 客户无收入来源 加入申请灰名单-30天 */
    const R02OJIIV = 'R02OJIIV';
    /** 申请人电话无人接听 拒绝 */
    const R02OMPNA = 'R02OMPNA';
    /** 申请人拒绝核实信息 加入申请灰名单-30天 */
    const R02OTCNV = 'R02OTCNV';
    /** 联系人电话无法接通 第一次：待补件 第二次：拒绝 */
    const R02RMPNA = 'R02RMPNA';
    /** 联系人核实异常 加入申请灰名单-180天 */
    const R02RTCDF = 'R02RTCDF';
    /** 申请人拒绝补充联系人 拒绝 */
    const R02OTCNI = 'R02OTCNI';
    /** 联系人拒绝核实信息 第一次：待补件 第二次：拒绝 */
    const R02RTCNV = 'R02RTCNV';
    /** 申请人号码异常 加入申请灰名单-30天 */
    const R01OMPCD = 'R01OMPCD';
    /** 联系人号码异常 第一次：待补件 第二次：拒绝 */
    const R01RMPCD = 'R01RMPCD';
    /** 申请人身份存疑 加入申请灰名单-180天 */
    const R02OTCDF = 'R02OTCDF';
    /** 非本人设备申请 拒绝 */
    const R01FS = 'R01FS';
    /** 申请人的家庭地址是假的 加入申请灰名单-30天 */
    const R02OHADF = 'R02OHADF';
    /** 地址不详细、不完整  拒绝*/
    const R02OHANV = 'R02OHANV';
    /** 工作信息造假 加入申请灰名单-30天 */
    const R02OJIDF = 'R02OJIDF';
    /** 申请人没有有效的社交网络账号 第一次：待补件 第二次：拒绝 */
    const R02OSAIV = 'R02OSAIV';
    /** 客户对金额的减少不满意 拒绝 */
    const R02OTCNC = 'R02OTCNC';

    /** 人脸搜索 */
    const R03OIDOB = 'R03OIDOB'; // TODO 待确认
    const R03OIDFF = 'R03OIDFF';

    /** 风控黑名单 */
    /** 本人证件号码命中内部黑名单 */
    const R04OIDIB = 'R04OIDIB';
    /** 本人电话号码命中内部黑名单 */
    const R04OPNIB = 'R04OPNIB';
    /** 本人Email命中内部黑名单 */
    const R04OEMIB = 'R04OEMIB';
    /** 本人deviceid、cookieid、advertisingid、persistentDeviceId任一命中内部黑名单 */
    const R04OMPIB = 'R04OMPIB';
    /** 本人银行卡号命中内部黑名单 */
    const R04OBAIB = 'R04OBAIB';
    /** 本人姓名|出生日期组合命中内部黑名单 */
    const R04ONMIB = 'R04ONMIB';
    /** 联系人电话号码或单位电话命中内部黑名单 */
    const R04RPNIB = 'R04RPNIB';
    /** ip命中内部黑名单 */
    const R04OIPIB = 'R04OIPIB';
    /** 银行卡号段命中内部黑名单 */
    const R04OBBIB = 'R04OBBIB';

    /** 本人证件号码命中跨品牌黑名单 */
    const R04OIDOB = 'R04OIDOB';
    /** 本人电话号码命中跨品牌黑名单 */
    const R04OPNOB = 'R04OPNOB';
    /** 本人Email命中跨品牌黑名单 */
    const R04OEMOB = 'R04OEMOB';
    /** 本人deviceid、cookieid、advertisingid、persistentDeviceId任一命中跨品牌黑名单 */
    const R04OMPOB = 'R04OMPOB';
    /** 本人银行卡号命中跨品牌黑名单 */
    const R04OBAOB = 'R04OBAOB';
    /** 本人姓名|出生日期组合命中跨品牌黑名单 */
    const R04ONMOB = 'R04ONMOB';
    /** 联系人电话号码或单位电话命中跨品牌黑名单 */
    const R04RPNOB = 'R04RPNOB';
    /** ip命中跨品牌黑名单 */
    const R04OIPOB = 'R04OIPOB';
    /** 银行卡号段命中跨品牌黑名单 */
    const R04OBBOB = 'R04OBBOB';

    /*************************************************************************************************************
     * Rule end
     ************************************************************************************************************/

    /** 上报风控黑名单RejectCode */
    const NEED_RISK_BLACKLIST = [
        self::R01OCPFF,
        self::R03DL,
        self::R02OHBNI,
        #self::R01OMPCD,
        self::R02OTCDF,
        self::R04OIDIB,
        self::R04OPNIB,
        self::R04OEMIB,
        #self::R04OMPIB,
        self::R04OBAIB,
        self::R04ONMIB,
        self::R04RPNIB,
        self::R04OIPIB,
        self::R04OBBIB,
        self::R04OIDOB,
        self::R04OPNOB,
        self::R04OEMOB,
        #self::R04OMPOB,
        self::R04OBAOB,
        self::R04ONMOB,
        self::R04RPNOB,
        self::R04OIPOB,
        self::R04OBBOB,
    ];

    /**
     * 风控黑名单关键字和拒绝码全局映射
     */
    const BLACKLIST_REFUSAL_CODE_GLOBAL = [
        RiskBlacklist::KEYWORD_ID_CARD_NO => self::R04OIDOB,
        RiskBlacklist::KEYWORD_BANKCARD => self::R04OBAOB,
        RiskBlacklist::KEYWORD_BANKCARD_CUT3 => self::R04OBBOB,
        RiskBlacklist::KEYWORD_BANKCARD_CUT4 => self::R04OBBOB,
        RiskBlacklist::KEYWORD_BANKCARD_CUT5 => self::R04OBBOB,
        RiskBlacklist::KEYWORD_BANKCARD_CUT6 => self::R04OBBOB,
        RiskBlacklist::KEYWORD_IP => self::R04OIPOB,
        RiskBlacklist::KEYWORD_DEVICE_ID => self::R04OMPOB,
        RiskBlacklist::KEYWORD_COOKIE_ID => self::R04OMPOB,
        RiskBlacklist::KEYWORD_ADVERTISING_ID => self::R04OMPOB,
        RiskBlacklist::KEYWORD_PERSISTENT_DEVICE_ID => self::R04OMPIB,
        RiskBlacklist::KEYWORD_TELEPHONE => self::R04OPNOB,
        RiskBlacklist::KEYWORD_WORK_TELEPHONE => self::R04RPNOB,
        RiskBlacklist::KEYWORD_CONTACT_TELEPHONE => self::R04RPNOB,
        RiskBlacklist::KEYWORD_EMAIL => self::R04OEMOB,
        RiskBlacklist::KEYWORD_NAME_BIRTHDAY => self::R04ONMOB,
    ];

    /**
     * 风控黑名单关键字和拒绝码品牌内部映射
     */
    const BLACKLIST_REFUSAL_CODE_MERCHANT = [
        RiskBlacklist::KEYWORD_ID_CARD_NO => self::R04OIDIB,
        RiskBlacklist::KEYWORD_BANKCARD => self::R04OBAIB,
        RiskBlacklist::KEYWORD_BANKCARD_CUT3 => self::R04OBBIB,
        RiskBlacklist::KEYWORD_BANKCARD_CUT4 => self::R04OBBIB,
        RiskBlacklist::KEYWORD_BANKCARD_CUT5 => self::R04OBBIB,
        RiskBlacklist::KEYWORD_BANKCARD_CUT6 => self::R04OBBIB,
        RiskBlacklist::KEYWORD_IP => self::R04OIPIB,
        RiskBlacklist::KEYWORD_DEVICE_ID => self::R04OMPIB,
        RiskBlacklist::KEYWORD_COOKIE_ID => self::R04OMPIB,
        RiskBlacklist::KEYWORD_ADVERTISING_ID => self::R04OMPIB,
        RiskBlacklist::KEYWORD_PERSISTENT_DEVICE_ID => self::R04OMPIB,
        RiskBlacklist::KEYWORD_TELEPHONE => self::R04OPNIB,
        RiskBlacklist::KEYWORD_WORK_TELEPHONE => self::R04RPNIB,
        RiskBlacklist::KEYWORD_CONTACT_TELEPHONE => self::R04RPNIB,
        RiskBlacklist::KEYWORD_EMAIL => self::R04OEMIB,
        RiskBlacklist::KEYWORD_NAME_BIRTHDAY => self::R04ONMIB,
    ];

    /**
     * 按照拒贷码处理后续流程
     * @param $order
     * @param $refusalCode
     * @return bool
     */
    public function handleByOperation($order, $refusalCode)
    {
        /** @var Order $order */
        $order->refresh();
        /** 对应RefusalCode需上报风控黑名单 */
        if (in_array($refusalCode, self::NEED_RISK_BLACKLIST)) {
            /** 贷前指定拒绝码入黑 */
            RiskBlacklistServer::server()->systemAddBlack($order, RiskBlacklist::TYPE_REFUSAL_CODE, true);
        }
        $operation = ApproveRuleService::server()->getOperationByRule($refusalCode);
        switch ($operation) {
            case self::OPERATION_GREY_LIST_7DAYS:
                $expireDate = Carbon::now()->addDays(7)->toDateTimeString();
                UserBlackServer::server()->addCannotAuth($order->user->telephone, $operation, $expireDate);
                break;
            case self::OPERATION_GREY_LIST_30DAYS:
                $expireDate = Carbon::now()->addDays(30)->toDateTimeString();
                UserBlackServer::server()->addCannotAuth($order->user->telephone, $operation, $expireDate);
                break;
            case self::OPERATION_GREY_LIST_180DAYS:
                $expireDate = Carbon::now()->addDays(180)->toDateTimeString();
                UserBlackServer::server()->addCannotAuth($order->user->telephone, $operation, $expireDate);
                break;
            case self::OPERATION_GREY_LIST_FOREVER:
                $expireDate = Carbon::now()->addDays(36500)->toDateTimeString();
                UserBlackServer::server()->addCannotAuth($order->user->telephone, $operation, $expireDate);
                break;
            case self::OPERATION_REJECT:
                # 不需要处理
                break;
            case self::OPERATION_CANCEL:
                /** 被拒后取消订单处理 */
                //OrderServer::server()->systemCancel($order, $order->status);
                break;
        }
        return true;
    }

    /**
     * 审批操作链路定义
     * @param $rule
     * @return false|int|string
     */
    public function getOperationByRule($rule)
    {
        $operationMapping = [
            self::OPERATION_GREY_LIST_7DAYS => [
                self::R03WD,
                self::R01OMGNI
            ],
            self::OPERATION_GREY_LIST_30DAYS => [
                self::R01OHPIV,
                self::R03OCPIV,
                self::R01OHPDF,
                self::R03OWCCD,
                self::R03OWCDF2,
                self::R03OWCDF3,
                self::R01FH,
                self::R03OIDOL,
                self::R02OJIIV,
                self::R02OTCNV,
                self::R01OMPCD,
                self::R02OHADF,
                self::R02OJIDF
            ],
            self::OPERATION_GREY_LIST_180DAYS => [
                self::R01OCPSP,
                self::R03OAGUQ,
                self::R03OJIUQ,
                self::R01OIDOL,
                self::R02RTCDF,
                self::R02OTCDF
            ],
            self::OPERATION_GREY_LIST_FOREVER => [
                self::R01OCPFF,
                self::R03DL,
                self::R02OHBNI,
            ],
            self::OPERATION_REJECT => [
                self::R03OCPUQ,
                self::R03OHPUQ,
                self::R03OWCIV,
                self::R03OWCNI,
                self::R03OWCUQ,
                self::R03SG,
                self::R02OMPNA,
                self::R02RMPNA,
                self::R02OTCNI,
                self::R02RTCNV,
                self::R01RMPCD,
                self::R01FS,
                self::R02OHANV,
                self::R02OSAIV,
                self::R02OTCNC,
            ],
            self::OPERATION_CANCEL => [
                self::R02QB
            ]
        ];
        foreach ($operationMapping as $key => $val) {
            if (in_array($rule, $val)) {
                return $key;
            }
        }
        return false;
    }

    /**
     * 根据黑名单关键字获取拒绝码
     * @param $keyword
     * @param string $isGlobal
     */
    public function getCodeByBlacklistKeyword($keyword, $isGlobal = RiskBlacklist::IS_GLOBAL_NO)
    {
        return array_get($isGlobal == RiskBlacklist::IS_GLOBAL_YES ?
            self::BLACKLIST_REFUSAL_CODE_GLOBAL :
            self::BLACKLIST_REFUSAL_CODE_MERCHANT,
            $keyword);
    }
}
