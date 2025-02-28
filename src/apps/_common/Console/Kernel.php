<?php

namespace Common\Console;

use Common\Console\Commands\Approve\CheckReachCondition;
use Common\Console\Commands\Approve\CheckUserExpire;
use Common\Console\Commands\Approve\PushToPool;
use Common\Console\Commands\Channel\ChannelCountConsole;
use Common\Console\Commands\Collection\CollectioBlack;
use Common\Console\Commands\Collection\CollectionAssign;
use Common\Console\Commands\Collection\CollectionFx;
use Common\Console\Commands\Collection\CollectionYxyPush;
use Common\Console\Commands\Collection\CollectionYxyStop;
use Common\Console\Commands\CollectionNotice\CollectionNotice;
use Common\Console\Commands\CollectionStatistics\CollectionStatistics;
use Common\Console\Commands\Common\ChangeUserAuthOverdays;
use Common\Console\Commands\Common\DateRecordConsole;
use Common\Console\Commands\Common\RepaymentPlanRenewalFailed;
use Common\Console\Commands\Coupon\IssueStaticUpdate;
use Common\Console\Commands\Coupon\IssueSTwo;
use Common\Console\Commands\Coupon\IssueUFour;
use Common\Console\Commands\Coupon\IssueUOne;
use Common\Console\Commands\Crm\TaskUserSendedTypeChangeSMS;
use Common\Console\Commands\Crm\TaskUserSMS;
use Common\Console\Commands\DemoData\DemoDataConsole;
use Common\Console\Commands\Excel\KudosConsole;
use Common\Console\Commands\HeartBeat;
use Common\Console\Commands\HelperRun\OrderPassPush;
use Common\Console\Commands\Init\ConfigInit;
use Common\Console\Commands\Init\S1MigratetoS2;
use Common\Console\Commands\Marketing\MarketingSms;
use Common\Console\Commands\Merchant\CreateMerchant;
use Common\Console\Commands\Merchant\InitApp;
use Common\Console\Commands\Nbfc\Reporting;
use Common\Console\Commands\Order\AutoDaifu;
use Common\Console\Commands\Order\AutoDaifuOnce;
use Common\Console\Commands\Order\AutoDaikou;
use Common\Console\Commands\Order\FlowCollectionBad;
use Common\Console\Commands\Order\FlowOverdue;
use Common\Console\Commands\Order\OrderCancel;
use Common\Console\Commands\Order\OrderContractUpdate;
use Common\Console\Commands\Order\OrderDeduction;
use Common\Console\Commands\Order\OrderExpireRemind;
use Common\Console\Commands\Order\RepaymentPlanOverdueUpdate;
use Common\Console\Commands\Permissions\InitRoleConsole;
use Common\Console\Commands\Permissions\SaveMenuToFileConsole;
use Common\Console\Commands\Permissions\SyncMenuFromFileConsole;
use Common\Console\Commands\Repay\Repay;
use Common\Console\Commands\Risk\RiskBlacklistExec;
use Common\Console\Commands\Risk\SystemApprove;
use Common\Console\Commands\Risk\SystemApproveExec;
use Common\Console\Commands\Statistics\StatisticsData;
use Common\Console\Commands\Statistics\StatisticsLog;
use Common\Console\Commands\Test;
use Common\Console\Commands\Trade\QueryTrade;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use Risk\Common\Console\CreditReport\CreditReportParse;
use Risk\Common\Console\Init\RiskConfigInit;
use Risk\Common\Console\SystemApprove\ComputeRiskData;
use Risk\Common\Console\SystemApprove\SystemApprove as RiskSystemApprove;
use Risk\Common\Console\SystemApprove\SystemApproveSandbox;
use Risk\Common\Console\UserAssociated\UserAssociatedRecord;
use Common\Console\Commands\Crm\AutoScan;
use Common\Console\Commands\Coupon\Issue;
use Common\Console\Commands\Crm\MarketingSMS as MSMS;
use Common\Console\Commands\Crm\MarketingReport;
use Common\Console\Commands\Crm\BatchStatistics;
use Common\Console\Commands\Crm\TaskStatistics;
use Common\Console\Commands\Crm\TelemarketingStop;
use Common\Utils\Sms\SmsHelper;
use Common\Console\Commands\Payout\Manual;
use Common\Console\Commands\User\UserAuthCmd;
use Common\Console\Commands\Call\EventsListen;
use Common\Console\Commands\Channel\AppflySnap;
use Common\Console\Commands\Collection\CollectionAssignOptimize;
use Common\Console\Commands\Order\AutoSign;
use Common\Console\Commands\Staff\AutoLogoff;
use Common\Console\Commands\Call\CallTest;
use Common\Console\Commands\Columbia\DataImport;
use Common\Console\Commands\Columbia\SendAlertSms;
use Common\Console\Commands\Call\CallFile;
use Common\Console\Commands\User\DataImportTools;
use Common\Console\Commands\Focus\StatisticalProgram;
use Common\Console\Commands\Payout\Paying;
use Common\Console\Commands\Marketing\MarketingGsm;

class Kernel extends ConsoleKernel {

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Paying::class,
        StatisticalProgram::class,
        DataImportTools::class,
        SendAlertSms::class,
        DataImport::class,
        CallFile::class,
        CallTest::class,
        AutoLogoff::class,
        AutoSign::class,
        CollectionAssignOptimize::class,
        AppflySnap::class,
        Manual::class,
        UserAuthCmd::class,
        EventsListen::class,
        /* CRM营销短信 */
        MarketingReport::class,
        MSMS::class,
        TaskUserSMS::class,
        TaskUserSendedTypeChangeSMS::class,
        ChangeUserAuthOverdays::class,
        AutoScan::class,
        BatchStatistics::class,
        TaskStatistics::class,
        TelemarketingStop::class,
        /*         * s1迁移复贷客户到s2 * */
        S1MigratetoS2::class,
        /*         * 定时展期失败 * */
        RepaymentPlanRenewalFailed::class,
        /*         * 优惠券发放 * */
        Issue::class,
        IssueUFour::class,
        IssueUOne::class,
        IssueSTwo::class,
        IssueStaticUpdate::class,
        /*         * 营销 * */
        MarketingSms::class,
        /** 日期表每日记录 */
        DateRecordConsole::class,
        Test::class,
        /** 心跳检测 */
        HeartBeat::class,
        /** 配置初始化 */
        ConfigInit::class,
        /** 定时取消订单 */
        OrderCancel::class,
        /** 流转逾期状态 */
        FlowOverdue::class,
        /** 订单到期还款提醒 */
        OrderExpireRemind::class,
        /** 催收统计 */
        CollectionStatistics::class,
        /** 飞象手动催收 * */
        CollectionFx::class,
        /** 流转已坏帐状态 * */
        FlowCollectionBad::class,
        /** 催收分案 * */
        CollectionAssign::class,
        /** 渠道统计 * */
        ChannelCountConsole::class,
        /** 保存菜单 */
        SaveMenuToFileConsole::class,
        /** 同步菜单 */
        SyncMenuFromFileConsole::class,
        /** 初始化角色 */
        InitRoleConsole::class,
        /** 统计 */
        StatisticsLog::class,
        StatisticsData::class,
        /** 演示数据 */
        DemoDataConsole::class,
        /** 自动代付 */
        AutoDaifu::class,
        /** 单次代付(可用来测试放款渠道) */
        AutoDaifuOnce::class,
        /** 自动代扣 */
        AutoDaikou::class,
        /** 交易结果查询 */
        QueryTrade::class,
        /** 进入人审 */
        PushToPool::class,
        /** 催收通知 */
        CollectionNotice::class,
        /** 商户相关 */
        // 根据 merchant id 初始化 app
        InitApp::class,
        /** 创建商户 */
        CreateMerchant::class,
        /** 定时减免订单尾期 */
        OrderDeduction::class,
        OrderPassPush::class,
        RepaymentPlanOverdueUpdate::class,
        CollectioBlack::class,
        //合同更新
        OrderContractUpdate::class,
        /*         * ************************************* 风控-业务 ******************************** */
        // 机审初始化
        SystemApprove::class,
        // 机审任务执行
        SystemApproveExec::class,
        //手动上传NBFC
        Reporting::class,
        //手动生成还款链接
        Repay::class,
        /*         * ************************************* 风控-业务end ******************************** */

        /*         * ************************************* 风控 ******************************** */
        // 机审配置初始化
        RiskConfigInit::class,
        // 机审执行
        RiskSystemApprove::class,
        /** 机审沙盒模拟 */
        SystemApproveSandbox::class,
        // 用户关联记录写入
        UserAssociatedRecord::class,
        /** 征信报告解析 */
        CreditReportParse::class,
        // Experian跑批的征信报告解析存宽表
        // ExperianCreditReportParseBatch::class,
        /** test */
        // Experian跑批
        // ExperianBatchGet::class,

        /*         * ************************************* 风控end ******************************** */

        /* 一休云测试 */
        CollectionYxyPush::class,
        CollectionYxyStop::class,
        /* excel 处理 */
        KudosConsole::class,
        // 逾期入黑 new
        RiskBlacklistExec::class,
        // 风控数据计算
        ComputeRiskData::class,
        // 心跳检测
        HeartBeat::class,
        MarketingGsm::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function schedule(Schedule $schedule) {
        $startTime = date("Y-m-d H:i:s");
        if(app()->runningInConsole()){
            echo $startTime . ' START' . PHP_EOL;
        }
        /** tips:
         * 使用withoutOverlapping缓存默认一天(执行完删除),设置过期时间->withoutOverlapping(3) 单位minute,
         * 如果任务执行失败没有删除掉缓存,过期后缓存也会自动失效,不然会导致任务一直不执行
         * lumen schedule时间设置尽量用以下方式
         * ->everyMinute(); 每分钟运行一次任务
         * ->everyFiveMinutes(); 每五分钟运行一次任务
         * ->everyTenMinutes(); 每十分钟运行一次任务
         * ->everyThirtyMinutes(); 每三十分钟运行一次任务
         * ->hourly(); 每小时运行一次任务
         * ->daily(); 每天凌晨零点运行任务
         * ->dailyAt('13:00'); 每天13:00运行任务
         * ->twiceDaily(1, 13); 每天1:00 & 13:00运行任务
         * ->weekly(); 每周运行一次任务
         * ->monthly(); 每月运行一次任务
         */
        /** 日期表更新 */
        $schedule->command(DateRecordConsole::class)->daily()->withoutOverlapping()->runInBackground()->onOneServer();

        /** 定时取消订单 */
        $schedule->command(OrderCancel::class)->dailyAt('00:10', [date("Y-m-d", time()-86400)])->withoutOverlapping()->runInBackground()->onOneServer();

        /** 定时更新用户信息授权状体 **/
        $schedule->command(UserAuthCmd::class)->dailyAt('00:01')->withoutOverlapping()->runInBackground()->onOneServer();

        /** CRM 营销 * */
        $schedule->command(AutoScan::class)->dailyAt('00:01')->withoutOverlapping()->runInBackground()->onOneServer(); #同步用户状态

        $schedule->command(BatchStatistics::class)->everyTenMinutes()->withoutOverlapping()->runInBackground()->onOneServer(); #批次统计
        $schedule->command(MarketingReport::class)->hourlyAt('1')->withoutOverlapping()->runInBackground()->onOneServer(); #统计
        $schedule->command(MarketingReport::class)->hourlyAt('31')->withoutOverlapping()->runInBackground()->onOneServer(); #统计
        $schedule->command(MSMS::class)->hourlyAt('0')->withoutOverlapping()->runInBackground()->onOneServer(); #短信发送
        $schedule->command(TaskStatistics::class)->everyTenMinutes()->withoutOverlapping()->runInBackground()->onOneServer(); #任务统计
        $schedule->command(TelemarketingStop::class)->hourlyAt('0')->withoutOverlapping()->runInBackground()->onOneServer(); #电销停止

//        $schedule->command(CallFile::class)->hourlyAt('0')->withoutOverlapping()->runInBackground()->onOneServer(); #通话文件更新

        # 催收前置外呼
//        $schedule->command(CallTest::class,["--type=collection"])->dailyAt('08:00')->withoutOverlapping()->runInBackground()->onOneServer();
//        $schedule->command(CallTest::class,["--type=approve"])->dailyAt('08:01')->withoutOverlapping()->runInBackground()->onOneServer();
        $schedule->command(CallTest::class,["--keepon"])->everyTenMinutes()->withoutOverlapping()->runInBackground()->onOneServer();

        /** 定时减免余额小于20的订单 */
        $schedule->command(OrderDeduction::class)->dailyAt('23:00')->withoutOverlapping()->runInBackground()->onOneServer();

        $schedule->command(Repay::class)->dailyAt('23:00')->withoutOverlapping()->runInBackground()->onOneServer();

        /** 自动下线 */
        $schedule->command(AutoLogoff::class)->dailyAt('23:50')->withoutOverlapping()->runInBackground()->onOneServer();

        /** 每日更新逾期天数 */
        $schedule->command(RepaymentPlanOverdueUpdate::class)->dailyAt('00:05')->withoutOverlapping()->runInBackground()->onOneServer();
        /** 每日更新应还金额 */
        $schedule->command(RepaymentPlanOverdueUpdate::class,[1])->dailyAt('00:15')->withoutOverlapping()->runInBackground()->onOneServer();

        /** 流转逾期状态 */
        $schedule->command(FlowOverdue::class)->daily()->withoutOverlapping()->runInBackground()->onOneServer();
        /** 流转已坏帐状态 * */
        $schedule->command(FlowCollectionBad::class)->daily()->withoutOverlapping()->runInBackground()->onOneServer();
        /** 催收分案 * */
        $schedule->command(CollectionAssign::class)->dailyAt('00:10')->withoutOverlapping()->runInBackground()->onOneServer();
        /** 催收再分案 * */
        $schedule->command(CollectionAssignOptimize::class)->dailyAt('00:30')->withoutOverlapping()->runInBackground()->onOneServer();

//        $schedule->command(CollectionYxyPush::class)->dailyAt('02:00')->withoutOverlapping()->runInBackground()->onOneServer();
        /** 催收统计 */
        $schedule->command(CollectionStatistics::class,["--type=statistics_staff", '--is_today'])->everyTenMinutes()->withoutOverlapping(60)->runInBackground()->onOneServer();
        $schedule->command(CollectionStatistics::class,["--type=statistics_staff", '--date='.date("Y-m-d", time()-86400)])->dailyAt('01:15')->withoutOverlapping(60)->runInBackground()->onOneServer();
        $schedule->command(CollectionStatistics::class,["--type=statistics_online"])->everyTenMinutes()->withoutOverlapping(60)->runInBackground()->onOneServer();
        /** 短信营销-还款次日召回 * */
        $schedule->command(MarketingSms::class)->dailyAt('10:00')->withoutOverlapping()->runInBackground()->onOneServer();
        /** 催收通知start */
//        // 到期前4-2天
        $schedule->command(CollectionNotice::class, [
            'sms', CollectionNotice::DAY_BEFORE_EXPIRE
        ])->dailyAt('10:00')->withoutOverlapping(5)->runInBackground()->onOneServer();
        $schedule->command(CollectionNotice::class, [
            'sms', CollectionNotice::DAY_BEFORE_EXPIRE
        ])->dailyAt('15:00')->withoutOverlapping(5)->runInBackground()->onOneServer();
        // 到期前3-2天
        $schedule->command(CollectionNotice::class, [
            'sms', CollectionNotice::DAY_BEFORE_EXPIRE
        ])->dailyAt('10:00')->withoutOverlapping(5)->runInBackground()->onOneServer();
        $schedule->command(CollectionNotice::class, [
            'sms', CollectionNotice::DAY_BEFORE_EXPIRE
        ])->dailyAt('15:00')->withoutOverlapping(5)->runInBackground()->onOneServer();
        //到期前一天
        $schedule->command(CollectionNotice::class, [
            'sms', CollectionNotice::DAY_WILL_EXPIRE
        ])->dailyAt('10:10')->withoutOverlapping(5)->runInBackground()->onOneServer();
        //到期当天
        $schedule->command(CollectionNotice::class, [
            'sms', CollectionNotice::DAY_EXPIRE
        ])->dailyAt("10:00")->withoutOverlapping(5)->runInBackground()->onOneServer();
        $schedule->command(CollectionNotice::class, [
            'sms', CollectionNotice::DAY_OVERDUE_FIRST_DAY
        ])->twiceDaily(12, 17)->withoutOverlapping(5)->runInBackground()->onOneServer();
        $schedule->command(CollectionNotice::class, [
            'sms', CollectionNotice::DAY_OVERDUE_FIRST_WEEK
        ])->twiceDaily(12, 17)->withoutOverlapping(5)->runInBackground()->onOneServer();
        /** 催收通知end */
        /** 数据统计 * */
        $schedule->command(StatisticsLog::class)->dailyAt('00:10')->withoutOverlapping()->runInBackground()->onOneServer();
        $schedule->command(StatisticsData::class)->dailyAt('00:20')->withoutOverlapping()->runInBackground()->onOneServer();

        /** 自动代付 */
        $schedule->command(AutoDaifu::class)->everyFiveMinutes()->withoutOverlapping()->runInBackground()->onOneServer();
        $schedule->command(AutoSign::class)->everyFiveMinutes()->withoutOverlapping()->runInBackground()->onOneServer();

        /*         * ************************************** 风控-业务 ******************************************* */
        /** 机审初始化 */
        $schedule->command(SystemApprove::class)->everyFiveMinutes()->withoutOverlapping(5)->runInBackground()->onOneServer();
        /** 机审任务执行 */
        $schedule->command(SystemApproveExec::class)->everyFiveMinutes()->withoutOverlapping(5)->runInBackground()->onOneServer();

        /*         * ************************************** 风控 ******************************************* */
        /** 风控机审 */
//        $schedule->command(RiskSystemApprove::class)->everyMinute()->withoutOverlapping(30)->runInBackground()->onOneServer();
//       $schedule->command(ComputeRiskData::class)->everyMinute()->withoutOverlapping(30)->runInBackground()->onOneServer();
        /** 用户关联记录写入 */
        //$schedule->command(UserAssociatedRecord::class)->hourly()->withoutOverlapping(15)->runInBackground()->onOneServer();
        /** 风控黑名单 */
        $schedule->command(RiskBlacklistExec::class)->dailyAt('00:20')->withoutOverlapping(15)->runInBackground()->onOneServer();
        /** 征信报告解析存宽表 * */
//        $schedule->command(CreditReportParse::class)->dailyAt('00:30')->withoutOverlapping()->runInBackground()->onOneServer();
        /** 每日发送还款还款邮件 * */
//        $schedule->command(KudosConsole::class)->dailyAt('00:30')->withoutOverlapping()->runInBackground()->onOneServer();
        /** 自动短信任务 */
        $schedule->command(TaskUserSMS::class)->everyFiveMinutes()->withoutOverlapping()->runInBackground()->onOneServer();
        $schedule->command(TaskUserSendedTypeChangeSMS::class)->everyFiveMinutes()->withoutOverlapping()->runInBackground()->onOneServer();
        /** 每天晚上定时把当天未展期的展期失效 **/
        $schedule->command(RepaymentPlanRenewalFailed::class)->dailyAt('00:02')->withoutOverlapping()->runInBackground()->onOneServer();
        //每天晚上发优惠券
        $schedule->command(Issue::class)->dailyAt('00:40')->withoutOverlapping()->runInBackground()->onOneServer();
        $schedule->command(IssueUFour::class)->dailyAt('01:40')->withoutOverlapping()->runInBackground()->onOneServer();
        $schedule->command(IssueUOne::class)->dailyAt('02:40')->withoutOverlapping()->runInBackground()->onOneServer();
        $schedule->command(IssueSTwo::class)->dailyAt('03:40')->withoutOverlapping()->runInBackground()->onOneServer();
        $schedule->command(IssueStaticUpdate::class)->dailyAt('04:40')->withoutOverlapping()->runInBackground()->onOneServer();

        # appflyers 入库
//        $schedule->command(AppflySnap::class)->dailyAt('00:01')->withoutOverlapping()->runInBackground()->onOneServer();

        //到期当天
        $schedule->command(CollectionNotice::class, [1])->dailyAt("07:00")->withoutOverlapping()->runInBackground()->onOneServer();
        $schedule->command(CollectionNotice::class, [2])->dailyAt("12:00")->withoutOverlapping()->runInBackground()->onOneServer();
        $schedule->command(CollectionNotice::class, [3])->dailyAt("18:00")->withoutOverlapping()->runInBackground()->onOneServer();
        $schedule->command(CollectionNotice::class, [4])->dailyAt("21:00")->withoutOverlapping()->runInBackground()->onOneServer();

        //每天做一次心跳检测
        $schedule->command(HeartBeat::class)->dailyAt("07:45")->withoutOverlapping()->runInBackground()->onOneServer();
        //每天查询paying状态
//        $schedule->command(Paying::class)->dailyAt("23:45")->withoutOverlapping()->runInBackground()->onOneServer();


//        $schedule->command(MarketingGsm::class)->hourly()->withoutOverlapping()->runInBackground()->onOneServer();

        /** 审批入审 */
        $this->approveSchedule($schedule);
    }

    /**
     * 审批用到的定时任务
     *
     * @param Schedule $schedule
     */
    protected function approveSchedule(Schedule $schedule) {
        $commands = [
            CheckUserExpire::class,
            PushToPool::class,
            CheckReachCondition::class,
        ];

        array_push($this->commands, ...$commands);
        /** 审批超时取消
        $schedule->command('approve:check-user-expire')
                ->everyMinute()
                ->withoutOverlapping(3)
                ->runInBackground();
         *
         *
         */

        $schedule->command('approve:push-to-pool')
                ->everyFiveMinutes()
                ->withoutOverlapping(3)
                ->runInBackground()
                ->onOneServer();

        $schedule->command('approve:check-reach-condition')
                ->everyFiveMinutes()
                ->withoutOverlapping(3)
                ->runInBackground()
                ->onOneServer();
    }

}
