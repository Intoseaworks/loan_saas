<?php

namespace Common\Services\OrderAgreement;

class RenewalAgreementTemplate
{
    const FIX_FIELD = [
        'repayment_plan_no',
        'order_no',
    ];

    public static $content = <<<CONTENT
续期申请
还款编号:{{repayment_plan_no}}

原始借款信息
借款协议编号:{{order_no}}
借款人：{{fullname}}
出借人：{{admin_account_fullname}}
借款本金：{{principal}}元
还款日期：{{previous_appointment_paid_time}}

续期生效前必须结清及支付费用(单位：元)
续期利息：{{renewal_interest}}
逾期利息：{{overdue_interest}}

续期申请
续期申请日期：{{renewal_apply_date}}
续期次数：{{renewal_issue}}
续期期限：{{renewal_days}}
续期后还款日期：{{new_appointment_paid_time}}

续期后借款信息更新(单位：元)
还款日期：{{new_appointment_paid_time}}
到期应还：{{receivable_amount}}

1、借款人（续期申请人）自愿向出借人及“{{app_name}}”平台就上述原始借款申请续期，并承诺在续期前结清上述费用。
2、续期自出借人及“{{app_name}}”收到借款人支付的续期费用且“{{app_name}}”平台更新借款人信息后生成。
3、借款人完全理解并同意，续期系对借款人债务的展期，平台方“{{app_name}}”会对借款人信用再次评估，故会另行产生综合费用。
4、借款人完全理解并同意，续期成功后，出借人及“{{app_name}}”会更新还款日期，借款人应按时还款。
借款人（续期申请人）：{{fullname}}
身份证号：{{id_card_no}}

本续期申请自借款人在“{{app_name}}”客户端点击“确认支付”时，视为提交申请，且不可撤销。

CONTENT;
}
