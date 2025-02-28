<?php

namespace Risk\Common\Services\SystemApproveData;

use Common\Services\BaseService;
use Common\Utils\Data\StringHelper;
use Common\Utils\DingDing\DingHelper;
use Illuminate\Support\Facades\DB;
use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Models\Business\UserData\UserContactsTelephone;
use Risk\Common\Models\RiskData\HighRiskPhonenumber;
use Risk\Common\Services\Risk\RiskAppHitServer;

class SystemApproveDataServer extends BaseService
{
    /**
     * 通讯录名称比对贷款app入高危电话库
     * @param $appId
     * @param $orderId
     * @return bool
     * @throws \Exception
     */
    public function contactLoanAppComparison($appId, $orderId)
    {
        $connectionName = (new HighRiskPhonenumber())->getConnectionName();
        DB::connection($connectionName)->beginTransaction();
        try {
            $order = Order::getByIdAndAppId($orderId, $appId);

            $contactFullnames = UserContactsTelephone::query()->where('user_id', $order->user_id)
                ->where('app_id', $order->app_id)
                ->pluck('contact_fullname', 'contact_telephone')
                ->toArray();

            $intersect = [];
            foreach ($contactFullnames as $phonenumber => $name) {
                $phonenumber = StringHelper::formatTelephone($phonenumber);
                $name = RiskAppHitServer::server()->comparisonContactNameList2($name);
                if ($name) {
                    $intersect[$phonenumber] = $name;
                }
            }

            $time = date('Y-m-d H:i:s');

            $models = HighRiskPhonenumber::query()->where('type', HighRiskPhonenumber::TYPE_LOAN_APP)
                ->whereIn('phonenumber', array_keys($intersect))
                ->get();

            // 暂时单条更新。后续优化成批量更新
            foreach ($models as $model) {
                if (isset($intersect[$model->phonenumber])) {
                    $model->update(['name' => $intersect[$model->phonenumber], 'last_dt' => $time, 'times_cnt' => $model->times_cnt + 1]);
                }
                unset($intersect[$model->phonenumber]);
            }

            $insertData = [];
            foreach ($intersect as $k => $v) {
                $insertData[] = [
                    'type' => HighRiskPhonenumber::TYPE_LOAN_APP,
                    'name' => $v,
                    'phonenumber' => $k,
                    'init_dt' => $time,
                    'last_dt' => $time,
                    'times_cnt' => 1,
                ];
            }

            HighRiskPhonenumber::insert($insertData);
            DB::connection($connectionName)->commit();
        } catch (\Exception $e) {
            DB::connection($connectionName)->rollBack();
            DingHelper::notice("app_id:{$appId} order_id:{$orderId} \n" . $e->getFile() . "\n" . $e->getLine() . "\n" . $e->getMessage(), '通讯录名称比对贷款app入高危电话库异常');
            return false;
        }

        return true;
    }
}
