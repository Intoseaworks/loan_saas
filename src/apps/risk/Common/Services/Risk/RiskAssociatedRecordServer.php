<?php

namespace Risk\Common\Services\Risk;

use Common\Services\BaseService;
use Common\Utils\DingDing\DingHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Models\Business\Order\RepaymentPlan;
use Risk\Common\Models\Business\User\User;
use Risk\Common\Models\Business\UserData\UserPhoneHardware;
use Risk\Common\Models\RiskData\RiskAssociatedRecord;
use Risk\Common\Models\RiskData\RiskAssociatedRecordVar;

class RiskAssociatedRecordServer extends BaseService
{
    protected $user;
    protected $order;

    protected $orders;
    protected $repaymentPlans;

    protected $associatedValue;

    protected $accountId;

    public function __construct(User $user, Order $order = null)
    {
        $this->user = $user;
        $this->order = $order;

        $this->orders = $user->orders;
    }

    public function addRiskAssociatedRecord()
    {
        try {
            $record = $this->getRecord();

            if (!$record) {
                $record = RiskAssociatedRecord::addRecord();
            }

            $associatedValue = $this->getAssociatedValue(true);
            $appendValue = $this->getAppendValue();
            $record->recordVar(array_merge($associatedValue, $appendValue));

            $this->accountId = $record->id;

            return true;
        } catch (\Exception $e) {
            DingHelper::notice([
                'user_id' => $this->user->id,
                'order_id' => optional($this->order)->id,
                'associated_value' => $this->associatedValue,
            ], '风控关联比对执行错误');
            return false;
        }
    }

    public function getRecord()
    {
        $associatedValue = $this->getAssociatedValue();

        $record = RiskAssociatedRecordVar::getRecordVarByValue($associatedValue)->groupBy('risk_associated_record_id');

        if ($record->isEmpty()) {
            return null;
        }

        // 找匹配项大于等于2，且权重和占比最高的记录
        $id = $record
            ->filter(function ($item) {
                return count($item) >= 2;
            })
            ->sortByDesc(function ($item) {
                return $item->sum('weight');
            })
            ->keys()
            ->first();

        $record = RiskAssociatedRecord::query()->find($id);
        if (!$id || !$record) {
            return null;
        }

        return $record;
    }

    protected function getAssociatedValue($readCache = false)
    {
        if ($readCache && $this->associatedValue) {
            return $this->associatedValue;
        }

        $associatedType = array_keys(RiskAssociatedRecordVar::ASSOCIATED_TYPE);
        $this->associatedValue = $this->takeExecByType($associatedType);

        return $this->associatedValue;
    }

    protected function takeExecByType($type)
    {
        $valueArr = [];
        foreach ($type as $k => $v) {
            $method = 'take' . Str::studly(Str::lower($v));
            if (!method_exists($this, $method)) {
                continue;
            }
            $value = call_user_func([$this, $method]);
            if (is_array($value)) {
                $value = array_map('trim', $value);
            } else {
                $value = trim($value);
            }
            $valueArr[$v] = $value;
        }
        return array_filter($valueArr);
    }

    protected function getAppendValue()
    {
        return $this->takeExecByType(RiskAssociatedRecordVar::APPEND_TYPE);
    }

    public function getAccountId()
    {
        return $this->accountId;
    }

    protected function takeAadhaar()
    {
        return optional($this->user->userInfo)->aadhaar_card_no;
    }

    protected function takePancard()
    {
        return optional($this->user->userInfo)->pan_card_no;
    }

    protected function takeTelephone()
    {
        return $this->user->telephone;
    }

    protected function takeBankcard()
    {
        return optional($this->user->bankCard)->no;
    }

    protected function takeVoteid()
    {
        return optional($this->user->userInfo)->voter_id_card_no;
    }

    protected function takePassport()
    {
        return optional($this->user->userInfo)->passport_no;
    }

    protected function takeImei()
    {
        $phoneHardware = UserPhoneHardware::getByUser($this->user->id);

        return optional($phoneHardware)->imei;
    }

    protected function takeEmail()
    {
        return optional($this->user->userInfo)->email;
    }

    protected function takeOrderNo()
    {
        return optional($this->order)->order_no;
    }

    protected function takeIssuedOrderNo()
    {
        if (!$this->orders instanceof Collection) {
            return null;
        }

        return $this->orders
            ->whereIn('status', Order::CONTRACT_STATUS)
            ->pluck('order_no')
            ->toArray();
    }

    protected function takeMaxOverdueDays()
    {
        if (!$this->orders instanceof Collection) {
            return null;
        }

        $repaymentPlans = $this->_getAllRepaymentPlan();

        return $repaymentPlans->max('overdue_days');
    }

    private function _getAllRepaymentPlan()
    {
        if (isset($this->repaymentPlans)) {
            return $this->repaymentPlans;
        }

        $repaymentPlans = RepaymentPlan::getByUserId($this->user->id);

        return $repaymentPlans;
    }

    protected function takeMaxIssuedAmount()
    {
        if (!$this->orders instanceof Collection) {
            return null;
        }

        return $this->orders->max('paid_amount');
    }

    protected function takeAppId()
    {
        return optional($this->user)->app_id;
    }
}
