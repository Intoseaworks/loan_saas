<?php

namespace Common\Console\Commands\Excel;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Common\Export\RepaymentExport;
use Illuminate\Support\Facades\Mail;

class KudosConsole extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rzp:daily:repay';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'rzp每日还款';

    /**
     * @return void
     */
    public function handle() {
        $filePath = 'RAZORPAY------Urupee daily repay data ' . date("Ymd") . '.xlsx';
        Excel::store(new RepaymentExport(), $filePath);
        $content = 'Hi Kudos team, 
Here is the repayment record of Digital Life, please help to transfer the principal back to our Razorpay account as soon as possible.
This is the Razorpay account details:
    A/C Holder：Kudos finance and investment pvt. ltd.
    A/C Number： 3434582392983188
    IFSC:  ICIC0000104';
        $toMail = ['nick.long@scoreonetech.com', 'nio.wang@scoreonetech.com', 'cassie.wang@scoreonetech.com', 'srinivas@digitallifetech.in'];
        Mail::raw($content, function ($message) use ($toMail, $filePath) {
            $message->subject($filePath);
            $attachment = storage_path('app/' . $filePath);
            // 在邮件中上传附件
            $message->attach($attachment, ['as' => $filePath]);
            $message->to($toMail);
        });
    }

}
