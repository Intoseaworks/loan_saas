<?php

return [
    '4560A8' => "{{captcha}} is your verification code, welcome to register in {{appName}}, this code will expire in 5 minutes. ", //验证码sms//
    '0C9706' => "You are using a dynamic code {{captcha}} to reset your password. Make sure it is used by yourself. This dynamic code iwill expire in 5 mins.", //修改密码验证码//
    '0C9705' => "Reminder of {{appName}}, your final approved loan amount is php {{amount}}, and will be disbursed to your designated account.", //修改额度触发//
    '096DA8' => "Dear user: You have been successfully approved {{amount}}PHP/{{loan_days}} days, funds will arrive at your designated account within 2 working days.", //审批通过成功短信//
    "219851" => "Reminder of {{appName}}, your Control No. is:{{withdraw_no}}, use it to claim cash with your valid ID at any {{channel}} branch.", //放款短信-线下取款客户//
    "B64AAD" => "Reminder!{{appName}} loan of PHP{{amount}} was disbursed to your designated account,please check your account.Your repayment Reference No. is {{reference_no}}.", //放款成功短信-线上取款客户//
//    "B64DDD" => "Congrats! Your loan of {{amount}}PHP/{{loan_days}} days is successfully disbursed. Repay via DragonPay Partners  7-11,LBC,Bayad Center, Cebuana.Dragonpay Ref. Number {{reference_no}}.", //放款成功短信-线下取款客户//
    "B64DDD" => "Congrats! Your loan of {{amount}}PHP/{{loan_days}} days is successfully disbursed. Repay via SkyPay Partners  7-11,ML,RD,ECPay, GCash,FastLoan,Paymaya Ref. Number {{reference_no}}.", //放款成功短信-线下取款客户//
    "B64ADD" => "Your Control No. is {{withdraw_no}}, which will expire in {{days}}, claim cash with your valid ID at any {{channel}} branch", //放款短信-线下取款客户skypayl渠道再次提醒客户取款 执行放款后的1,3,5,7天 7:00//
    "B64ADC" => "Reminder of {{appName}}, the disbursement was failed due to bank problems or expired Control No.,pls reapply if needed.", //放款短信-放款失败（核心状态为撤销）//
    # 20210520 Tracy 更新
    "65A8AF" => "Sir/Madam, your loan is about to expire, please process the payment on time. The first 20% of customers repay today can re-borrow up to 12000 pesos. If interested you can apply directly after the repayment, if you have questions, please contact us", //到期T-2提醒短信模板 9：00//
    "65A8AD" => "Sir/Madam, your loan expired TODAY, please process it before 12:00pm  so you can be qualified for the first 20% lucky customers. According to our customers credit score, re-borrowing up to 12000 pesos line of credit will be possible. If interested please apply directly after repayment. if you have questions, please contact us", //到期T提醒短信模板  7：00//
    "B49B8B" => "Your {{appName}} loan: php{{amount}} is overdued already.You might missed your due date. kindly pay your balance ASAP to maintain a good credit standing", //逾期T1  9:00//
    "E84A90" => "Warning: No payment from you was received despite all reminders. Pls Pay your balance ASAP to avoid futher collection efforts. Thank you", //逾期T+3  9:00//
    "ACCFCD" => "Dear client, thanks for using {{appName}} loan, We will always be happy to serve you again. Reash us whenever you need us!", //结清客户仅在结清次日10:00发送/
    "ACCFCE" => "You have successfully applied for a extension, please pay in full today: {{amount}} to avoid rollover failure, thank you for your cooperation", //展期预约//
    "B64AAA" => "Dear {{appName}} users,you submitted information does not meet the approval criteria, please resubmission of this information on {{appName}} APP within {{days}} days", //补件//
];
