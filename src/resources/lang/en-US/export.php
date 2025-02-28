<?php

use Admin\Exports\Collection\CollectionOrderExport;
use Admin\Exports\CollectionStatistics\CollectionStatisticsExport;
use Admin\Exports\OperateData\BalanceOfPaymentsExport;
use Admin\Exports\OperateData\PostLoanExport;
use Admin\Exports\Order\ContractExport;
use Admin\Exports\Order\OrderExport;
use Admin\Exports\Repayment\RepaymentPlanExport;
use Admin\Exports\TradeManage\TradeLogExport;
use Admin\Exports\User\UserExport;

return [
    // 用户
    UserExport::class => [
        // 用户反馈
        UserExport::SCENE_FEEDBACK_LIST => [
            '真实姓名' => 'Name',
            '手机号码' => 'Phone',
            '用户类型' => 'Customer Type',
            '意见' => 'Suggestion',
            '意见提交时间' => 'Feedback Time',
        ],
        UserExport::SCENE_SUPER_NOT_AUTH_USER => [
            '手机号码' => 'Phone',
            '注册时间' => 'regsiterTime',
            '注册终端' => 'clientId',
            '渠道' => 'Channel',
        ],
    ],

    OrderExport::class => [
        OrderExport::SCENE_BORROW => [
            '手机号码' => 'Phone',
            '真实姓名' => 'Name',
            '用户类型' => 'Customer Type',
            '订单号' => 'Order No',
            '借款金额' => 'Loan Amount',
            '借款期限' => 'Loan Tenure',
            '手续费(包含GST)' => 'Processing Fee(INCL GST)',
            '实际到账金额' => 'Disbursement Amount',
            '订单创建时间' => 'Application Time',
            '版本号' => 'Version',
            '业务处理时间' => 'Latest System Record',
            '订单进展状态' => 'Status',
        ],
        OrderExport::SCENE_SUPER_NOT_SIGN_ORDER => [
            '手机号码' => 'Telephone',
            '真实姓名' => 'Fullname',
            '订单创建时间' => 'Created Time',
            '订单通过时间' => 'Pass Time',
            '订单进展状态' => 'Order Status',
        ],
    ],

    // 支付记录
    TradeLogExport::class => [
        // 支付记录
        TradeLogExport::SCENE_TRADE_LOG => [
            '手机号码' => 'Phone',
            '收款人姓名' => 'Name',
            '订单号' => 'Order No',
            '商户交易编号' => 'Merchant Transaction No',
            '支付平台交易编号' => 'Payment Platform Transaction No',
            '金额' => 'Transaction Amount',
            '银行卡号' => 'Bank Account',
            '银行名称' => 'Bank Name',
            '支行名称' => 'Branch Name',
            'IFSC Code' => 'IFSC Code',
            '省/市' => 'State/ City',
            '打款时间' => 'Disbursement Time',
            '还款时间' => 'Repayment Time',
            '类型' => 'Type',
            '支付方式' => 'Payments',
            '状态' => 'Status',
        ],
        // 系统放款记录
        TradeLogExport::SCENE_SYSTEM_PAY_LIST => [
            '手机号码' => 'Phone',
            '收款人姓名' => 'Name',
            '订单号' => 'Order No',
            '交易编号' => 'Transaction No',
            '借款金额' => 'Loan Amount',
            '手续费' => 'Processing Fee',
            'GST' => 'GST',
            '出款金额' => 'Disbursement Amount',
            '收款银行卡' => 'Bank Account',
            '打款时间' => 'Disbursement Time',
            '支付方式' => 'Payments',
            '状态' => 'Status',
            '描述' => 'Describe',
        ]
    ],

    RepaymentPlanExport::class => [
        RepaymentPlanExport::SCENE_REPAYMENT_LIST => [
            '手机号码' => 'Phone',
            '姓名' => 'Name',
            '订单号' => 'Order No',
            '期数' => 'Period',
            '放款日期' => 'Lending Date',
            '实际还款日期' => 'Actual Repayment Time',
            '应还款日期' => 'Repayement Time',
            '状态' => 'Status',
            '借款金额' => 'Loan Amount',
            '逾期天数' => 'Overdue days',
            '应还逾期罚息(包含GST)' => 'Penalty(INCL GST)',
            '减免金额' => 'Dedution Amount',
            '当期本金' => 'Current Principal',
            '当期利息' => 'Current Interest',
            '应还本息' => 'Repayment Amount',
            '实际还款金额' => 'Actual Repayment Amount',
        ],
        RepaymentPlanExport::SCENE_REPAYMENT_PAID_LIST => [
            '手机号码' => 'Phone',
            '收款人姓名' => 'Name',
            '订单号' => 'Order No',
            '借款金额' => 'Loan Amount',
            '借款期限' => 'Loan Tenure',
            '订单创建时间' => 'Application Time',
            '手续费(包含GST)' => 'Processing Fee(INCL GST)',
            '出款金额' => 'Disbursement Amount',
            '逾期罚息' => 'Penalty',
            '实际还款金额' => 'Actual Repayment Amount',
            '状态' => 'Status',
        ],
        RepaymentPlanExport::SCENE_REPAYMENT_OVERDUE_LIST => [
            '手机号码' => 'Phone',
            '收款人姓名' => 'Name',
            '订单号' => 'Order No',
            '借款金额' => 'Loan Amount',
            '订单创建时间' => 'Application Time',
            '手续费(包含GST)' => 'Processing Fee(INCL GST)',
            '出款金额' => 'Disbursement Amount',
            '应还逾期罚息(包含GST)' => 'Penalty(INCL GST)',
            '应还金额' => 'Repayment Amount',
            '逾期天数' => 'Overdue days',
            '催收等级' => 'Collection Level',
            '催收员' => 'Operator',
        ],
        RepaymentPlanExport::SCENE_REPAYMENT_BAD_DEBT_LIST => [
            '手机号码' => 'Phone',
            '收款人姓名' => 'Name',
            '订单号' => 'Order No',
            '借款金额' => 'Loan Amount',
            '借款期限' => 'Loan Tenure',
            '订单创建时间' => 'Application Time',
            '手续费(包含GST)' => 'Processing Fee(INCL GST)',
            '出款金额' => 'Disbursement Amount',
            '应还金额' => 'Repayment Amount',
            '订单坏账时间' => 'Bad Debts Time',
            '坏账规则' => 'Bad Debts Rules',
        ],
    ],

    CollectionOrderExport::class => [
        CollectionOrderExport::SCENE_COLLECTION_ORDER_LIST => [
            '订单号' => 'Order No',
            '收款人姓名' => 'Fullname',
            '手机号码' => 'Telephone',
            '借款金额 (元)' => 'Principal',
            '借款期限 (天)' => 'Loan Tenure',
            '应还款日期' => 'Due Date',
            '分案时间' => 'Assign Time',
            '逾期天数' => 'Overdue Days',
            '应还逾期罚息(包含GST)' => 'Penalty(Incl GST)',
            '减免金额 (元)' => 'Deduction Amount',
            '应还本息 (元)' => 'Repayment Amount',
            '催收等级' => 'Collection Level',
            '催收员' => 'Operator',
            '状态' => 'Status',
            '催记时间' => 'Collection Time',
            '联系人' => 'Contact',
            '联系结果' => 'Call Result',
            '催记记录' => 'Remarks',
            '承诺还款时间' => 'Repayment Promise Time',
            '催收成功时间' => 'Case Closed Time',
            '坏账时间' => 'Bad Debt Time',
            '现居住地址' => 'Current Address',
            '永久居住地址' => 'Permanent Address',
        ],
    ],

    CollectionStatisticsExport::class => [
        CollectionStatisticsExport::SCENE_ORDER_LIST => [
            '日期' => 'Date',
            '累计订单数' => 'Accumulated Order',
            '累计成功数' => 'Accumulated Repayment Order',
            '累计坏账数' => 'Accumulated Bad Debts',
            '累计催回率（%）' => 'Accumulated Collect Repayment rate（%）',
            '今日新增订单数' => 'case added today',
            '今日承诺还款订单数' => 'promised repayment orders today',
            '今日成功订单数' => 'case closed orders today',
            '今日坏账订单数' => 'bad debt orders today',
            '今日催回率（%）' => 'Today CollectRepayment rate（%）',
        ],
        CollectionStatisticsExport::SCENE_RATE_LIST => [
            '日期' => 'Date',
            '逾期总订单数' => 'Total number of overdue orders',
            '当前逾期订单数' => 'Current number of overdue orders',
            '当日回款成功数' => 'successful collect repayments on collection day',
            '截止当前回款成功数' => 'successful collcect repayment orders',
            '坏账数' => 'Bad debts',
            '当日回款率（%）' => 'Day collect repayment Rate（%）',
            '截止当前回款率（%）' => 'Current collect repayment Rate（%）',
            '催收记录次数' => 'Number of collection records',
        ],
    ],

    // 运营
    PostLoanExport::class => [
        // 每日贷后分析
        PostLoanExport::SCENE_LIST => [
            '放款日期' => 'Date',
            '放款笔数' => 'Number of Loans',
            '放款金额' => 'Loan amount',
            '未还款笔数' => 'Number of non-repayment',
            '未还款金额' => 'Amount of non-repayment',
            '还款笔数' => 'Number of repayments',
            '还款金额' => 'Amount of repayment',
            '逾期还款笔数' => 'Number of overdue repayments',
            '逾期还款占比' => 'Proportion of overdue repayments',
            '1天+逾期单' => '1 day + overdue orders',
            '1天+逾期率' => '1 day + overdue rate',
            '3天+逾期单' => '3 day + overdue orders',
            '3天+逾期率' => '3 day + overdue rate',
            '7天+逾期单' => '7 day + overdue orders',
            '7天+逾期率' => '7 day + overdue rate',
            '坏账数' => 'Bad Debts',
            '坏账率' => 'Bad Debts rate',
        ],
    ],

    // 借款管理
    ContractExport::class => [
        // 放款订单
        ContractExport::SCENE_LOAN => [
            '手机号码' => 'Phone',
            '真实姓名' => 'Applicant',
            '用户类型' => 'Customer Type',
            '借款次数' => 'Number of borrowing',
            '逾期次数' => 'Number of overdue',
            '省份' => 'State',
            '市' => 'City',
            '订单号' => 'Order No',
            '借款金额' => 'Loan Amount',
            '借款期限' => 'Loan Tenure',
            '打款时间' => 'Disbursement Time',
            '手续费(包含GST)' => 'Processing Fee(INCL GST)',
            '放款金额' => 'Disbursement Amount',
            '应还息费' => 'Interest',
            '应还逾期罚息(包含GST)' => 'Penalty(INCL GST)',
            '应还金额' => 'Repayment Amount',
            '实际还款金额' => 'Actual Repayment Amount',
            '实际还款时间' => 'Actual Repay Time',
            '版本号' => 'Version',
            '状态' => 'Status',
        ],
    ],

    //运营数据
    BalanceOfPaymentsExport::class => [
        BalanceOfPaymentsExport::SCENE_INCOME => [
            '姓名' => 'Payer',
            '第三方交易编号' => 'Transaction No.',
            '合同编号' => 'Contract No.',
            '实还本金' => 'Actual repayment principal',
            '实还息费' => 'Interest',
            '实还罚息' => 'Penalty',
            '实还罚息GST' => 'GST-Penaltye',
            '实际还款时间' => 'Actual Repayment Date)',
            '支付方式' => 'Payments',
            '状态' => 'Status',
        ],
        BalanceOfPaymentsExport::SCENE_DISBURSE => [
            '姓名' => 'Borrower',
            '第三方交易编号' => 'Transaction No.',
            '合同编号' => 'Contract No.',
            '放款金额' => 'Disbursement Amount',
            '平台账户' => 'Bank Account',
            '打款时间' => 'Disbursement Time',
            '支付方式' => 'Payments',
            '状态' => 'Status',
        ]
    ],
];
?>
