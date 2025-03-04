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
            '真实姓名' => 'الاسم',
            '手机号码' => 'رقم الهاتف',
            '用户类型' => 'نوع العميل',
            '意见' => 'اقتراح',
            '意见提交时间' => 'وقت تقديم اقتراحات',
        ],
        UserExport::SCENE_SUPER_NOT_AUTH_USER => [
            '手机号码' => 'رقم الهاتف',
            '注册时间' => 'وقت التسجيل',
            '注册终端' => 'التسجيل الطرفي',
            '渠道' => 'القناة',
        ],
    ],
    OrderExport::class => [
        OrderExport::SCENE_BORROW => [
            '手机号码' => 'رقم الهاتف',
            '真实姓名' => 'الاسم',
            '用户类型' => 'نوع العميل',
            '订单号' => 'رقم الطلب',
            '借款金额' => 'مبلغ القرض',
            '借款期限' => 'فترة القرض',
            '手续费(包含GST)' => 'رسوم المعالجة (شاملة ضريبة السلع والخدمات)',
            '实际到账金额' => 'مبلغ الصرف',
            '订单创建时间' => 'وقت إنشاء الطلب',
            '版本号' => 'رقم الإصدار',
            '业务处理时间' => 'وقت معالجة الأعمال',
            '订单进展状态' => 'حالة تقدم الطلب',
        ],
        OrderExport::SCENE_SUPER_NOT_SIGN_ORDER => [
            '手机号码' => 'رقم الهاتف',
            '真实姓名' => 'الاسم كاملا',
            '订单创建时间' => 'وقت انشاء الطلب',
            '订单通过时间' => 'وقت تمرير الطلب',
            '订单进展状态' => 'حالة تقدم الطلب',
        ],
    ],
    // 支付记录
    OrderExport::class => [
        // 支付记录
        TradeLogExport::SCENE_TRADE_LOG => [
            '手机号码' => 'رقم الهاتف',
            '收款人姓名' => 'اسم المستفيد',
            '订单号' => 'رقم الطلب',
            '商户交易编号' => 'رقم معاملة التاجر',
            '支付平台交易编号' => 'رقم معاملة منصة الدفع',
            '金额' => 'المبلغ',
            '银行卡号' => 'رقم حساب البنك ',
            '银行名称' => 'اسم البنك',
            '支行名称' => 'اسم الفرع',
            'IFSC Code' => 'كود IFSC',
            '省/市' => 'مدينة/ المحافظة',
            '打款时间' => 'وقت الصرف',
            '还款时间' => 'وقت السداد',
            '类型' => 'النوع',
            '支付方式' => 'طرق الدفع',
            '状态' => 'الحالة',
        ],
        // 系统放款记录
        TradeLogExport::SCENE_SYSTEM_PAY_LIST => [
            '手机号码' => 'رقم الهاتف',
            '收款人姓名' => 'اسم المستفيد',
            '订单号' => 'رقم الطلب',
            '交易编号' => 'رقم العملية التجارية',
            '借款金额' => 'مبلغ القرض',
            '手续费' => 'كلفة الإجراءات',
            'GST' => 'GST',
            '出款金额' => 'مبلغ الصرف',
            '收款银行卡' => 'بطاقة البنك المستلم',
            '打款时间' => 'وقت الصرف',
            '支付方式' => 'طرق الدفع',
            '状态' => 'الحالة',
            '描述' => 'الوصف',
        ]
    ],
    RepaymentPlanExport::class => [
        RepaymentPlanExport::SCENE_REPAYMENT_LIST => [
            '手机号码' => 'رقم الهاتف',
            '姓名' => 'الاسم',
            '订单号' => 'رقم الطلب',
            '期数' => 'الفترة',
            '放款日期' => 'تاريخ الإقراض',
            '实际还款日期' => 'وقت السداد الفعلي',
            '应还款日期' => 'وقت السداد',
            '状态' => 'الحالة',
            '借款金额' => 'مبلغ القرض',
            '逾期天数' => 'الأيام المتأخرة',
            '应还逾期罚息(包含GST)' => 'عقوبة تأخر السداد(شاملة ضريبة السلع والخدمات)',
            '减免金额' => 'مبلغ الاستقطاع',
            '当期本金' => 'العملة الحالية',
            '当期利息' => 'الفائدة الحالية',
            '应还本息' => 'مبلغ السداد',
            '实际还款金额' => 'مبلغ السداد الفعلي',
        ],
        RepaymentPlanExport::SCENE_REPAYMENT_PAID_LIST => [
            '手机号码' => 'رقم الهاتف',
            '收款人姓名' => 'اسم المستلم',
            '订单号' => 'رقم الطلب',
            '借款金额' => 'مبلغ القرض',
            '借款期限' => 'فترة القرض',
            '订单创建时间' => 'وقت انشاء الطلب',
            '手续费(包含GST)' => 'رسوم المعالجة (شاملة ضريبة السلع والخدمات)',
            '出款金额' => 'مبلغ الصرف',
            '逾期罚息' => 'عقوبة التأخر',
            '实际还款金额' => 'مبلغ السداد الفعلي',
            '状态' => 'الحالة',
        ],
        RepaymentPlanExport::SCENE_REPAYMENT_OVERDUE_LIST => [
            '手机号码' => 'رقم الهاتف',
            '收款人姓名' => 'اسم المستلم',
            '订单号' => 'رقم الطلب',
            '借款金额' => 'مبلغ القر',
            '订单创建时间' => 'وقت انشاء الطلب',
            '手续费(包含GST)' => 'رسوم المعالجة (شاملة ضريبة السلع والخدمات)',
            '出款金额' => 'مبلغ الصرف',
            '应还逾期罚息(包含GST)' => 'عقوبة تأخر السداد(شاملة ضريبة السلع والخدمات)',
            '应还金额' => 'مبلغ السداد',
            '逾期天数' => 'الأيام المتأخرة',
            '催收等级' => 'مستوى التحصيل',
            '催收员' => 'المحصل',
        ],
        RepaymentPlanExport::SCENE_REPAYMENT_BAD_DEBT_LIST => [
            '手机号码' => 'رقم الهاتف',
            '收款人姓名' => 'اسم المستلم',
            '订单号' => 'رقم الطلب',
            '借款金额' => 'مبلغ القرض',
            '借款期限' => 'فترة القرض',
            '订单创建时间' => 'وقت انشاء الطلب',
            '手续费(包含GST)' => 'رسوم المعالجة (شاملة ضريبة السلع والخدمات)',
            '出款金额' => 'مبلغ الصرف',
            '应还金额' => 'مبلغ السداد',
            '订单坏账时间' => 'وقت طلب الديون المعدومة',
            '坏账规则' => 'قواعد الديون المعدومة',
        ],
    ],
    CollectionOrderExport::class => [
        CollectionOrderExport::SCENE_COLLECTION_ORDER_LIST => [
            '订单号' => 'رقم الطلب',
            '收款人姓名' => 'الاسم كاملا',
            '手机号码' => 'رقم الهاتف',
            '借款金额 (元)' => 'مبلغ القرض (يوان) ',
            '借款期限 (天)' => 'فترة القرض (يوم) ',
            '应还款日期' => 'تاريخ السداد',
            '分案时间' => 'وقت التقسيم',
            '逾期天数' => 'الايام المتأخرة',
            '应还逾期罚息(包含GST)' => 'عقوبة تأخر السداد(بما في ذلك ضريبة السلع والخدمات)',
            '减免金额 (元)' => 'مبلغ الاستقطاع (يوان) ',
            '应还本息 (元)' => 'أصل مبلغ السداد والفائدة المستحقة(يوان) ',
            '催收等级' => 'مستوى التحصيل',
            '催收员' => 'المحصل',
            '状态' => 'الحالة',
            '催记时间' => 'وقت التحصيل',
            '联系人' => 'التواصل',
            '联系结果' => 'نتيجة التواصل',
            '催记记录' => 'ملاحظات التحصيل',
            '承诺还款时间' => 'وقت وعد السداد',
            '催收成功时间' => 'وقت نجاح التحصيل',
            '坏账时间' => 'وقت الديون المعدومة',
            '现居住地址' => 'العنوان الحالي',
            '永久居住地址' => 'العنوان الدائم',
        ],
    ],
    CollectionStatisticsExport::class => [
        CollectionStatisticsExport::SCENE_ORDER_LIST => [
            '日期' => 'التاريخ',
            '累计订单数' => 'عدد الطلب المتراكم',
            '累计成功数' => 'عدد النجاح المتراكم',
            '累计坏账数' => 'الديون المعدومة المتراكمة',
            '累计催回率（%）' => 'تحصيل معدل السداد المتراكم （٪ ',
            '今日新增订单数' => 'عدد الطلبات الجديدة الزائدة اليوم',
            '今日承诺还款订单数' => 'طلبات السداد الموعودة اليوم',
            '今日成功订单数' => 'عدد الطلبات الناجحة اليوم',
            '今日坏账订单数' => 'عدد الطلبات المعدومة اليوم ',
            '今日催回率（%）' => 'تحصيل معدل السداد اليوم （٪） ',
        ],
        CollectionStatisticsExport::SCENE_RATE_LIST => [
            '日期' => 'التاريخ',
            '逾期总订单数' => 'إجمالي عدد الطلبات المتأخرة',
            '当前逾期订单数' => 'العدد الحالي للطلبات المتأخرة',
            '当日回款成功数' => 'تحصيل المدفوعات بنجاح في يوم التحصيل',
            '截止当前回款成功数' => 'تحصيل أوامر السداد الناجحة حتى الوقت الحالي',
            '坏账数' => 'عدد الديون المعدومة',
            '当日回款率（%）' => 'معدل سداد يوم جمع السداد （٪） ',
            '截止当前回款率（%）' => 'معدل سداد التحصيل الحالي （٪） ',
            '催收记录次数' => 'عدد سجلات التحصيل',
        ],
    ],
    // 运营
    PostLoanExport::class => [
        // 每日贷后分析
        PostLoanExport::SCENE_LIST => [
            '放款日期' => 'تاريخ القرض',
            '放款笔数' => 'عدد القروض',
            '放款金额' => 'مبلغ القرض',
            '未还款笔数' => 'عدد حالات عدم السداد',
            '未还款金额' => 'مبلغ عدم السداد',
            '还款笔数' => 'عدد التسديدات',
            '还款金额' => 'مبلغ السداد',
            '逾期还款笔数' => 'عدد المدفوعات المتأخرة',
            '逾期还款占比' => 'نسبة المدفوعات المتأخرة',
            '1天+逾期单' => 'يوم واحد + الطلبات المتأخرة',
            '1天+逾期率' => 'يوم واحد + معدل التأخير',
            '3天+逾期单' => '3 أيام + الطلبات المتأخرة',
            '3天+逾期率' => '3 أيام + معدل التأخير',
            '7天+逾期单' => '7 أيام + الطلبات المتأخرة',
            '7天+逾期率' => '7 أيام + معدل التأخير',
            '坏账数' => 'عدد الديون المعدومة',
            '坏账率' => 'معدل الديون المعدومة',
        ],
    ],
    // 借款管理
    ContractExport::class => [
        // 放款订单
        ContractExport::SCENE_LOAN => [
            '手机号码' => 'رقم الهاتف',
            '真实姓名' => 'الاسم الحقيقي',
            '用户类型' => 'نوع العميل',
            '借款次数' => 'عدد الاقتراض',
            '逾期次数' => 'عدد المتأخرين',
            '省份' => 'المحافظة',
            '市' => 'المدينة',
            '订单号' => 'رقم الطلب',
            '借款金额' => 'مبلغ القرض',
            '借款期限' => 'فترة القرض',
            '打款时间' => 'وقت الصرف',
            '手续费(包含GST)' => 'رسوم المعالجة (شاملة ضريبة السلع والخدمات)',
            '放款金额' => 'مبلغ الصرف',
            '应还息费' => 'فائدة السداد',
            '应还逾期罚息(包含GST)' => 'عقوبة تأخر السداد(شاملة ضريبة السلع والخدمات)',
            '应还金额' => 'مبلغ السداد',
            '实际还款金额' => ' مبلغ السداد الفعلي ',
            '实际还款时间' => 'وقت السداد الفعلي',
            '版本号' => 'رقم الإصدار',
            '状态' => 'الحالة',
        ],
    ],
    //运营数据
    BalanceOfPaymentsExport::class => [
        BalanceOfPaymentsExport::SCENE_INCOME => [
            '姓名' => 'اسم الدافع',
            '第三方交易编号' => 'رقم معاملة الطرف الثالث.',
            '合同编号' => 'رقم العقد.',
            '实还本金' => 'أصل السداد الفعلي',
            '实还息费' => 'فائدة السداد الفعلي',
            '实还罚息' => 'الغرامة الفعلية ',
            '实还罚息GST' => 'ضريبة السلع والخدمات وغرامة عدم السداد الفعلي ',
            '实际还款时间' => 'تاريخ السداد الفعلي',
            '支付方式' => 'طرق الدفع',
            '状态' => 'الحالة',
        ],
        BalanceOfPaymentsExport::SCENE_DISBURSE => [
            '姓名' => 'اسم المستلف',
            '第三方交易编号' => 'رقم معاملة الطرف الثالث.',
            '合同编号' => 'رقم العقد.',
            '放款金额' => 'مبلغ الصرف',
            '平台账户' => 'حساب المنصة',
            '打款时间' => 'وقت الصرف',
            '支付方式' => 'طرق الدفع',
            '状态' => 'الحالة',
        ]
    ],
];
