<?php

namespace Common\Services\Clm;

use Common\Exceptions\ApiException;
use Common\Models\Clm\ClmChangeLog;
use Common\Models\Clm\ClmCustomer;
use Common\Models\Clm\ClmLevel;
use Common\Models\Clm\ClmRule;
use Common\Models\Order\Order;
use Common\Models\User\User;
use Common\Models\User\UserWhitelist;
use Common\Services\BaseService;

class ClmServer extends BaseService
{

    const INIT_LEVEL = 1; //非白名单初始化等级
    const WHITE_INIT_LEVEL = 2; //白名单初始化等级

    const CLM_LEVEL = [
        'BRONZE' => [-2, -1, 0, 1, 2, 3, 4], //青铜
        'SILVER' => [5, 6, 7, 8], //白银
        'GOLD' => [9, 10, 11, 12, 13, 14], //黄金
        'DIAMOND' => [15, 16, 17, 18], //钻石
    ];

    public function getLevelByUid($userId)
    {
        $level = $this->getCustomerLevel($userId)->clmLevel;

        $res = [
            'loanCode' => '1001',
            'productCode' => 'A301',
            'level' => $this->searchAliceByLevel($level->clm_level),
            'minLoanTerms' => $level['loan_term_days_min'],
            'maxLoanTerms' => $level['loan_term_days_max'],
            'minAmount' => $level['loan_amount_min'],
            'maxAmount' => $level['loan_amount_max'],
            'amountStep' => $level['loan_amount_step'],
            'loanStep' => $level['loan_term_day_step'],
            'maxViewTerms' => $this->getSystemMaxLevel('loan_term_days_max'),
            'maxViewAmount' => $this->getSystemMaxLevel('loan_amount_max'),
            'loanTerms' => [
            ],
        ];
        for ($day = $res['minLoanTerms']; $day <= $res['maxViewTerms']; $day += $res['loanStep']) {
            $loanTerm = ['loanTerm' => $day];
            if ($day <= $res['maxLoanTerms']) {
                $loanTerm['available'] = true;
            } else {
                $loanTerm['available'] = false;
            }
            $res['loanTerms'][] = $loanTerm;
        }
        return $res;
    }

    /**
     * 获取用户等级
     * @param type $userId
     */
    public function getCustomerLevel($userId) {
        $customerLevel = ClmCustomer::model()->where('user_id', $userId)->first();
        if ($customerLevel) {
            return $customerLevel;
        } else {
            return $this->initCustomerLevel($userId);
        }
    }

    /**
     * 初始化用户等级
     */
    public function initCustomerLevel($userId) {
        $user = User::model()->getOne($userId);
        $isWhite = UserWhitelist::model()->inWhiteMenu($user->id_card_no, $user->card_type);
        $customer = [
            "merchant_id" => $user->merchant_id,
            "user_id" => $userId,
            "idcard_type" => $user->card_type,
            "idcard_no" => $user->id_card_no,
            "name" => $user->fullname,
            "phone" => $user->telephone,
            "current_level" => self::INIT_LEVEL,
            "max_level" => self::INIT_LEVEL,
            "created_at" => date("Y-m-d H:i:s"),
        ];
        if ($isWhite) {
            $customer['current_level'] = self::WHITE_INIT_LEVEL;
            $customer['max_level'] = self::WHITE_INIT_LEVEL;
        }
        return ClmCustomer::model()->createModel($customer);
    }

    /**
     * 升级
     * @param type $userId
     * @param type $orderId
     */
    public function upgradeLevel($userId, $orderId) {
        if (ClmChangeLog::model()->where("order_id", $orderId)->exists()) {
            throw new ApiException("Order recorded");
        }
        $user = User::model()->getOne($userId);
        $order = Order::model()->getOne($orderId);
        $currentLevel = $this->getCustomerLevel($userId);

        if (!in_array($order->status, Order::FINISH_STATUS)) {
            throw new ApiException("Order pending :" . $order->status);
        }
        if ($order->user_id == $user->id) {
            //获取升级规则
            $rules = ClmRule::model()->where('rule_type', 1)->get();
            $overdueDays = $order->getOverdueDays();
            $upLevel = $currentLevel->current_level;
            foreach ($rules as $rule) {
                $daysRange = json_decode($rule->value, true);
                if ($overdueDays >= $daysRange[0] && $overdueDays <= $daysRange[1]) {
                    eval('$upLevel ' . $rule->level_opt . ';');
                    break;
                }
            }
            //命中特殊规则
            $rules = ClmRule::model()->where('rule_type', 1)->get();
            $user->userWork->salary;
            $log = [
                "merchant_id" => $user->merchant_id,
                "user_id" => $user->id,
                "order_id" => $orderId,
                "old_level" => $currentLevel->current_level,
                "new_level" => $upLevel,
                "created_at" => date("Y-m-d H:i:s"),
            ];
            $maxLevel = $this->getSystemMaxLevel();
            $upLevel = $upLevel <= $maxLevel ? $upLevel : $maxLevel;
            if ($upLevel != $currentLevel->current_level && $upLevel <= $this->getSystemMaxLevel()) {
                $currentLevel->max_level = max([$upLevel, $currentLevel->max_level]);
                $currentLevel->current_level = $upLevel;
                $currentLevel->save();
            }
            ClmChangeLog::model()->createModel($log);
            return $currentLevel->clmLevel;
        } else {
            throw new ApiException("The order does not match the user");
        }
    }

    /**
     * 获取系统最高等级
     */
    public function getSystemMaxLevel($columnName = 'clm_level')
    {
        $res = ClmLevel::model()->newQuery()->orderByDesc($columnName)->first();
        return $res->$columnName;
    }

    /**
     * 根据等级获取别名
     * @param $level
     * @return int|string
     */
    public function searchAliceByLevel($level)
    {
        foreach (self::CLM_LEVEL as $key => $value) {
            if (in_array($level, $value)) {
                return $key;
            }
        }
        return 'BRONZE'; //默认青铜
    }
}
