<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/21
 * Time: 18:50
 */

namespace Admin\Services\Data;


use Admin\Models\Collection\Collection;
use Admin\Models\Order\Order;
use Admin\Models\Staff\Staff;
use Admin\Models\Trade\TradeLog;
use Admin\Models\Upload\Upload;
use Admin\Models\User\User;
use Admin\Models\User\UserContact;
use Admin\Models\User\UserInfo;
use Admin\Services\BaseService;
use Admin\Services\Collection\CollectionDeductionServer;
use Admin\Services\Collection\CollectionRecordServer;
use Admin\Services\Risk\RiskServer;
use Api\Models\BankCard\BankCardPeso;
use Common\Models\BankCard\BankCard;
use Common\Models\Order\ContractAgreement;
use Common\Models\Trade\AdminTradeAccount;
use Common\Models\Upload\Upload as CashNowUpload;
use Common\Utils\Upload\ImageHelper;

class DataServer extends BaseService
{
    const USER = 'user';
    const BANK_CARDS = 'bankCards';
    const ORDER = 'order';
    const ORDER_LIST = 'orderList';
    const DEDUCTION_HISTORY = 'deductionHistory';
    const ORDER_LIST_COUNT = 'orderListCount';
    const COLLECTION_RECORD_LIST = 'collectionRecordList';
    const COLLECTION_INFO = 'collectionInfo';
    const CONTRACT_LOG = 'contractLog';
    const CONTRACT_AGREEMENT = 'contractAgreement';
    const REPAY_LOG = 'repayLog';
    const METHOD_ALIAS = [
        self::USER => '基本信息',
        RiskServer::OPERATOR_REPORT => '运营商报告',
        self::BANK_CARDS => '银行卡',
        RiskServer::USER_POSITION => '位置信息',
        RiskServer::CONTACTS => '通讯录',
        RiskServer::USER_SMS => '短信记录',
        RiskServer::USER_APP => 'APP应用列表',
        self::ORDER => '借款信息',
        self::ORDER_LIST => '历史借款',
        self::DEDUCTION_HISTORY => '减免记录',
        self::COLLECTION_INFO => '催收任务',
        self::COLLECTION_RECORD_LIST => '借款人催收记录',
        self::CONTRACT_LOG => '放款记录',
        self::CONTRACT_AGREEMENT => '合同查看',
        self::REPAY_LOG => '还款记录',
        RiskServer::MANUAL_RISK => '人审风险报告',
        RiskServer::SYS_CHECK_DETAIL => '机审详情',
        RiskServer::DUO_TOU => '多头报告',
        RiskServer::ALIPAY_REPORT => '支付宝报告',
    ];
    public $data = [];
    private $user;
    private $order;

    /**
     * @param $userId
     * @param null $orderId
     * @throws \Common\Exceptions\ApiException
     */
    public function __construct($userId = null, $orderId = null)
    {
        if ($userId !== null && !$this->user = User::model()->getOne($userId)) {
            $this->outputException('用户数据不存在');
        }
        if ($orderId !== null && !$this->order = Order::model()->getOne($orderId)) {
            $this->outputException('订单数据不存在');
        }
    }

    public function list($tabLists)
    {
        $tabLists = (array)$tabLists;
        /** 检查是否设置查询方法 */
        $this->checkList($tabLists);
        /** 取出风控数据集合 */
        $riskMethods = array_intersect($tabLists, RiskServer::RISK_DATA);
        /** 排除风控数据 */
        $tabs = array_diff($tabLists, RiskServer::RISK_DATA);
        /** 遍历执行查询 */
        foreach ($tabs as $tab) {
            if (!method_exists($this, $tab)) {
                continue;
            }
            // echo $tab.':'.date('Y-m-d H:i:s').'<br>';
            call_user_func([$this, $tab]);
            // echo $tab.':'.date('Y-m-d H:i:s').'<br>';
        }
        // exit();
        /** 获取风控数据 */
        $this->getRiskData($this->user->id, $riskMethods);
        /** 格式化数据输出 */
        $this->formatData($tabLists);
        return (array)$this->data;
    }

    private function checkList($tabLists)
    {
        if ($diff = array_diff($tabLists, array_keys(self::METHOD_ALIAS))) {
            $this->outputException(implode(',', $diff) . '查询未设置');
        }
    }

    private function formatData($tabLists)
    {
        $tabs = [];
        foreach ($tabLists as $key => $val) {
            $tabs[$key]['label'] = t(self::METHOD_ALIAS[$val], 'data');
            $tabs[$key]['name'] = $val;
            if (!($dataCount = array_get($this->data, $val . '.count'))) {
                $data = array_get($this->data, $val, []);

                if ($data instanceof \Illuminate\Support\Collection) {
                    $dataCount = $data->count();
                } elseif (is_array($data)) {
                    $dataCount = count($data);
                } else {
                    $dataCount = 1;
                }
            }
            $tabs[$key]['count'] = $dataCount;
            //$tabs[$key]['count'] = array_get($this->data, $val . '.count') ?? count(array_get($this->data, $val, []));
        }
        return $this->data = [
            'tabs' => $tabs,
            'data' => $this->data,
        ];
    }

    /**
     * 获取风控数据
     * @param $userId
     * @param array $riskDataMethods
     * @return mixed
     */
    public function getRiskData($userId, $riskDataMethods = [])
    {
        return $this->data = array_merge_recursive($this->data,
            RiskServer::server()->getUserData($userId, $riskDataMethods));
    }

    /**
     * 用户
     * @return User
     */
    public function user()
    {
        foreach ($userContacts = $this->user->userContacts as $userContact) {
            $userContact->setScenario(UserContact::SCENARIO_DETAIL)->getText();
        }
        if ($userInfo = $this->user->userInfo) {
            $userInfo->setScenario(UserInfo::SCENARIO_DETAIL)->getText();
            $userInfo->education_level = \Common\Models\Common\Dict::getNameByCode($userInfo->education_level);
        }
        $this->user->setScenario(User::SCENARIO_DETAIL)->getText();
        $this->user->userInfo->pan_card_no = $this->user->id_card_no;
        //用户认证图片
        $this->user->auth_pics = $this->authPics();
        return $this->data[self::USER] = $this->user;
    }

    /**
     *
     * @return mixed
     */
    public function authPics()
    {
        $authPics = Upload::model()->getPathsByUserIdAndType($this->user->id, [
            Upload::TYPE_BUK_NIC,
            Upload::TYPE_FACES,
            Upload::TYPE_BUK_NIC_BACK,
        ]);
//        if (!count($authPics)) {
//            return null;
//        }
        foreach ($authPics as $type => $authPic) {
            $authPics[$type] = ImageHelper::getPicUrl($authPic, 900);
        }
        //获取用户对应证件类型证件照片
        $authPics['id_card'] = $authPics[Upload::TYPE_BUK_NIC] ?? "";
        $authPics['id_card_back'] = $authPics[Upload::TYPE_BUK_NIC_BACK] ?? "";
        return $authPics;
    }

    /**
     *
     * @return mixed
     */
    public function authPicOfUserCardType()
    {
        $imgRelation = array_flip(Upload::TYPE_BUK);
        if ( !isset($imgRelation[$this->user->card_type]) ){
            return null;
        }
        $authPicOfUserCardTypes = Upload::model()->getPathsByUserIdAndType($this->user->id, [
            $imgRelation[$this->user->card_type]
        ]);
        if (!count($authPicOfUserCardTypes)) {
            return null;
        }
        foreach ($authPicOfUserCardTypes as $type => $authPic) {
             return ImageHelper::getPicUrl($authPic, 900);
        }
    }

    /**
     * 用户银行卡列表
     * @return BankCard[]|\Illuminate\Database\Eloquent\Collection
     */
    public function bankCards()
    {
        foreach ($bankCards = $this->user->bankCards as $bankCard) {
            $bankCard->getText();
            $bankCard->fullname = $bankCard->account_name ?? '---';
            $bankCard->no = $bankCard->account_no ?? '---';
            $bankCard->payment_type = $bankCard->payment_type == BankCardPeso::PAYMENT_TYPE_OTHER ?  $bankCard->channel: $bankCard->payment_type;
        }
        unset($this->user->bankCards);
        return $this->data[self::BANK_CARDS] = $bankCards;
    }

    /**
     * 用户订单
     * @return Order|array|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function order()
    {
        $this->order && $this->order->setScenario(Order::SCENARIO_DETAIL)->getText();

        foreach ($orders = $this->user->manualOrders as $order) {
            $order->repay_msg = $order->repayMsg();
            $order->setScenario(Order::SCENARIO_LIST)->getText();
        }
        $this->order && $this->order->orderList = $orders;
        $this->order->control_no = $this->order->withdraw_no ?? "--";
        $this->order->cash_pickup_agency = $this->order->user->bankCard->instituion_name ?? '--';
        $this->order->dg_pay_lifetime_id = $this->order->user->userInfo->dg_pay_lifetime_id ?? null;
        $this->order->repay_code = $this->order->reference_no ?? null;

        unset($this->order->user);
        return $this->data[self::ORDER] = $this->order ?? [];
    }

    /**
     * 订单记录列表
     * @return \Common\Models\Order\Order[]|\Illuminate\Database\Eloquent\Collection
     */
    public function orderList()
    {
        foreach ($orders = $this->user->orders as $order) {
            $order->setScenario(Order::SCENARIO_LIST)->getText();
            if ($order->lastApprove) {
                $order->lastApprove->approve_content = $order->lastApprove->result . ' ' .$order->lastApprove->remark;
                $order->lastApprove->admin_username = Staff::model()->getNameById($order->lastApprove->admin_id);
            }
        }
        $this->data[self::ORDER_LIST_COUNT] = count($orders);
        $this->data['order_ids'] = $orders->pluck('id');
        return $this->data[self::ORDER_LIST] = $orders;
    }

    /**
     * 减免记录列表
     * @return \Common\Models\Order\Order[]|\Illuminate\Database\Eloquent\Collection
     */
    public function deductionHistory()
    {
        $params = [];
        $params['status'] = ['2', '3'];
        $params['order_ids'] = $this->user->orders->pluck('id')->toArray();
//        dd($params);
        return $this->data[self::DEDUCTION_HISTORY] = CollectionDeductionServer::server()->applyListAll($params);
//        return $this->data[self::DEDUCTION_HISTORY] = [5,8];
    }

    public function collectionInfo()
    {
        $collection = $this->order->collection;
        $collectionInfo = [];
        if ($collection) {
            $collectionInfo = $collection->setScenario(Collection::SCENARIO_DETAIL)->getText();
        }
        return $this->data[self::COLLECTION_INFO] = $collectionInfo;
    }

    /**
     * 催收记录列表
     * @return mixed
     */
    public function collectionRecordList()
    {
//        foreach ($collectionRecords = $this->order->collectionRecords as $collectionRecord) {
//            $collectionRecord->setScenario(CollectionRecord::SCENARIO_LIST)->getText();
//        }
        $collectionRecords = CollectionRecordServer::server()->getList(['order_id' => $this->order->id]);
        foreach ($collectionRecords as $collectionRecord) {
            $collectionRecord->getText();
        }
        return $this->data[self::COLLECTION_RECORD_LIST] = $collectionRecords;
    }

    /**
     * 放款合同
     * @return array|ContractAgreement[]|\Illuminate\Database\Eloquent\Collection|mixed
     */
    public function contractAgreement()
    {
        if ($contractAgreements = $this->order->contractAgreements) {
            foreach ($contractAgreements as $contractAgreement) {
                $contractAgreement->setScenario(ContractAgreement::SCENARIO_LIST)->getText();
            }
        }
        return $this->data[self::CONTRACT_AGREEMENT] = $contractAgreements ?? [];
    }

    /**
     * 放款记录
     * @return array|mixed
     */
    public function contractLog()
    {
        if ($tradeLogs = $this->order->remitTradeLogs) {
            foreach ($tradeLogs as $tradeLog) {
                $tradeLog->setScenario(TradeLog::SCENARIO_LIST)->getText();
            }
            unset($this->order->remitTradeLogs);
        }
        return $this->data[self::CONTRACT_LOG] = $tradeLogs ?? [];
    }

    /**
     * 还款记录
     * @return array|mixed
     */
    public function repayLog()
    {
        $tradeLogs = $this->order->receiptsTradeLogs;
        $tradeLogs = $tradeLogs->where('trade_result', TradeLog::TRADE_RESULT_SUCCESS)->values();

        if ($tradeLogs) {
            foreach ($tradeLogs as $tradeLog) {
                $tradeLog->adminTradeAccount && $tradeLog->adminTradeAccount->setScenario(AdminTradeAccount::SCENE_OPTION)->getText();
                $tradeLog->bankCard && $tradeLog->bankCard->setScenario(BankCard::SCENARIO_DETAIL)->getText();
                $tradeLog->setScenario(TradeLog::SCENARIO_LIST)->getText();

                if (strtotime($tradeLog->trade_result_time) <= 0) {
                    $tradeLog->trade_result_time = '';
                }
            }
            unset($this->order->receiptsTradeLogs);
        }
        return $this->data[self::REPAY_LOG] = $tradeLogs ?? [];
    }
}
