<?php

namespace Risk\Common\Console\Test;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Models\Business\User\UserInfo;
use Risk\Common\Models\CreditReport\ThirdExperian;
use Risk\Common\Services\CreditReport\CreditReportServer;

class ExperianBatchGet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:experian-batch-get {type} {--limit=} {--sql=} {--batchNo=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Experian 征信数据跑批';

    public function handle()
    {
        $limit = $this->option('limit');
        if (isset($limit) && !is_int($limit) && $limit <= 0) {
            $this->error('limit input error');
            return false;
        }

        $batchNo = $this->option('batchNo');
        if (!$batchNo) {
            $batchNo = date('YmdHis') . '|' . uniqid();
        }

        $type = $this->argument('type');

        switch ($type) {
            case 'excel':
                $this->execExcel($batchNo, $limit);
                break;
            case 'sql':
                $sql = $this->option('sql');

                if (!$sql) {
                    $this->error('sql 不能为空');
                    die();
                }

                $this->execSql($batchNo, $sql, $limit);
                break;
        }
    }

    public function execExcel($batchNo, $limit)
    {
        $excel = Excel::toArray(new \stdClass(), dirname(__FILE__) . '/experainList.xlsx');
        $list = $excel[0];

        $header = array_flip(array_shift($list));
        $genderList = ['Male' => 'M', 'Female' => 'F'];

        $cnt = 0;
        foreach ($list as $key => $item) {
            $phone = $item[$header['telephone']] ?? '';
            $gender = $item[$header['gender']] ?? '';
            $gender = array_get($genderList, $gender);
            $birthday = $item[$header['birthday']] ?? '';
            $dob = date('Y-m-d', strtotime(str_replace('/', '-', $birthday)));

            if (!$phone || ThirdExperian::query()->where('telephone', $phone)->exists()) {
                continue;
            }

            $params = [
                'user_id' => 0,
                'phone_no' => $phone,
                'name' => $item[$header['full_name']] ?? '',
                'pan_card_no' => $item[$header['pan_card_no']] ?? '',
                'gender' => $gender,
                'dob' => $dob,
            ];

            $model = $this->exec($batchNo, $params);

            $this->line($key . ":" . $model->remark);

            if (!is_null($limit) && ++$cnt > $limit) {
                break;
            }
        }

        $this->line('success! cnt:' . $cnt);
    }

    public function exec($batchNo, $params)
    {
        $server = CreditReportServer::server()->experianCreditReportBatch($batchNo, $params);

        return $server->getData();
    }

    public function execSql($batchNo, $sql, $limit)
    {
//        $sql = "select `order_id` as 'orderId' from `system_approve_record` where `module` = 'credit' and `description` LIKE '%stage2 request failed%'";

        $orderIds = array_unique(array_pluck(DB::select($sql), 'orderId'));
        $orders = Order::query()->whereIn('id', $orderIds)->get();

        if (count($orderIds) != $orders->count()) {
            $this->error('order id 数量不对应');
            die();
        }

        if (!$this->confirm('order数量:' . $orders->count())) {
            die();
        }

        $cnt = 0;
        foreach ($orders as $order) {
            $expireDays = CreditReportServer::REPORT_EXPIRE_DAYS;

            $user = $order->user;
            $userInfo = $user->userInfo;

            $phone = $user->telephone;
            $gender = $userInfo->gender == UserInfo::GENDER_FEMALE ? 'F' : ($userInfo->gender == UserInfo::GENDER_MALE ? 'M' : '');
            $dob = date('Y-m-d', strtotime(str_replace('/', '-', $userInfo->birthday)));

            $params = [
                'user_id' => $user->id,
                'phone_no' => $user->telephone,
                'name' => $user->fullname,
                'pan_card_no' => $userInfo->pan_card_no,
                'gender' => $gender,
                'dob' => $dob,
                'city' => $userInfo->city ?? '',
                'state' => $userInfo->province ?? '',
                'pincode' => $userInfo->pincode ?? '',
                'address' => $userInfo->address ?? '',
                'reuse_start_time' => date('Y-m-d H:i:s', strtotime("-{$expireDays}day")),
            ];

            if (!$phone || ThirdExperian::query()->where('telephone', $phone)->exists()) {
                continue;
            }

            $model = $this->exec($batchNo, $params);

            $this->line($order->id . ":" . $model->remark);

            if (!is_null($limit) && $cnt + 1 > $limit) {
                break;
            }
            $cnt++;
        }

        $this->line('success! cnt:' . $cnt);
    }
}
