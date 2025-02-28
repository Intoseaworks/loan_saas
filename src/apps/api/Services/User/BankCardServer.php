<?php

namespace Api\Services\User;

use Api\Models\BankCard\BankCard;
use Api\Models\Order\Order;
use Api\Services\Auth\Card\CardCheckServer;
use Api\Services\BaseService;
use Api\Services\Order\OrderDetailServer;
use Common\Models\Common\BankInfo;
use Common\Models\User\User;
use Common\Models\User\UserAuth;
use Common\Services\Order\OrderServer;
use Common\Utils\Lock\LockRedisHelper;
use Illuminate\Support\Facades\DB;

class BankCardServer extends BaseService
{
    const CODE_ERROR_NOT_PASS = 13000;//13100;
    const CODE_AUTH_FAIL = 13000;//13200;
    const CODE_REFUSE = 13000;//13300;

    public static $rejectDay = 90;

    public function index()
    {
        /** @var User $user */
        $user = \Auth::user();
        $status = $user->getBankCardStatus();
        if (!($bankCard = $user->bankCard) || $status != UserAuth::AUTH_STATUS_SUCCESS) {
            return $this->outputSuccess('未绑定银行卡', ['status' => 3]);
        }
        return $this->outputSuccess('success', $bankCard);
    }

    public function getIfscList($params)
    {
        $list = BankInfo::model()->search($params)->paginate(99999);

        return $list;
    }

    /**
     * @param User $user
     * @param $cardNo
     * @param $ifsc
     * @return BankCardServer|void
     * @throws \Common\Exceptions\ApiException
     */
    public function createBankCard(User $user, $cardNo, $ifsc)
    {
        /** IFSC自动转大写 */
        $ifsc = strtoupper($ifsc);

        if ($user->bankCard) {
            if ($user->bankCard->status == BankCard::STATUS_ACTIVE && $user->bankCard->account_no == $cardNo && $user->getBankCardStatus() == UserAuth::STATUS_VALID) {
                $this->outputException('This bank account has been bound to your account.');
            }
        }

        if ($user->order && $user->order->app_version == '1.0.3' && $user->order->isNotComplete() && !in_array($user->order->status, Order::PAY_FAIL_STATUS)) {
            $this->outputException('当前有进行中订单，无法换绑银行卡');
        }

        // 拒绝认证判断
        $this->checkRejecting($user);
        // 超过3次认证判断
        $this->checkIsMax($user);

        /** 防止银行卡并发请求  redis原子锁3秒 */
        if (!LockRedisHelper::helper()->userBankCardBind($user->id)) {
            $this->outputException('your bank account for verification.');
        }

        # 判读ifsc是否有效
        $bankInfo = $this->checkIfsc($ifsc);
        // 入库
        $bankCard = $this->addBankCard($user, $cardNo, $bankInfo);
        #####nio.wang 银行卡验证后移 #############
        if ("1" == $user->app_id) {
            # 第三方绑卡验证
            $aadhaarServer = CardCheckServer::server();
            $aadhaarServer->validateBankCard($cardNo, $ifsc, $user);
            if (!$aadhaarServer->isSuccess()) {
                $this->updateRejectCount($bankCard);
                # 5分钟请求重复不计入次数
                if (strpos($aadhaarServer->getMsg(), 'after 5 Minutes')) {
                    return $this->output($aadhaarServer->getCode(), $aadhaarServer->getMsg());
                }
//            $days = $this->updateRejectCount($bankCard);
//            if ($days > 0) {
//                # 失败3次
//                return $this->output(self::CODE_REFUSE,
//                    "Sorry, your bank account could not be verified even after several attempts. We will keep your account on hold for {$this::$rejectDay} days.");
//            }
                return $this->output($aadhaarServer->getCode(), $aadhaarServer->getMsg());
            }
        }

        // 银行卡状态流转为正常
        $bankCard->updateToAuthSuccess();
        //若用户最后一笔订单状态为放款失败 则更改状态为待放款
        $order = $user->order;
        if ($order && in_array($order->status, Order::PAY_FAIL_STATUS)) {
            OrderServer::server()->manualPayFailToManualPass($order->id);
            //更新最后一笔订单 对应卡号
            OrderDetailServer::server()->saveOrderBank($order);
        }
        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_BANKCARD);
        return $this->outputSuccess(t('银行卡绑定成功'), $bankCard);
    }

    /**
     * @param User $user
     * @param $no
     * @param BankInfo $bankInfo
     * @return bool|BankCard
     * @throws \Common\Exceptions\ApiException
     */
    public function addBankCard(User $user, $no, BankInfo $bankInfo)
    {
        DB::beginTransaction();

        $status = BankCard::STATUS_WAIT_AUTH;
        BankCard::clearStatus($user->id, $status);

        $params = [
            'user_id' => $user->id,
            'no' => $no,
            'name' => $user->fullname,
            //'reserved_telephone' => array_get($params, 'reserved_telephone'),
            'bank_name' => $bankInfo->bank,
            //'bank' => '',
            'bank_branch_name' => $bankInfo->branch,
            'status' => $status,
            'ifsc' => $bankInfo->ifsc,
            'city' => $bankInfo->city,
            'province' => $bankInfo->state,
        ];

        $model = Bankcard::model(Bankcard::SCENARIO_CREATE)->saveModel($params);

        if (!$model) {
            DB::rollBack();
            $this->outputException(t('银行卡绑定失败'));
        }

        DB::commit();
        return $model;
    }

    /**
     * 拒绝认证判断
     * @param User $user
     * @throws \Common\Exceptions\ApiException
     */
    public function checkRejecting(User $user)
    {
        // 拒绝认证判断
        if (BankCard::model()->isRejecting($user->id)) {
            $this->outputException(t('Failed to verify the account for 3 times, your account will be hold on for security'), self::CODE_REFUSE);
        }
    }

    /**
     * 超过两次认证判断
     * @param User $user
     * @throws \Common\Exceptions\ApiException
     */
    public function checkIsMax(User $user)
    {
        $bankCardCount = (new BankCard())->query()->withoutGlobalScope('bind_failed')->where('user_id', $user->id)->where('created_at', '>', date('Y-m-d'))->count();
        if ($bankCardCount >= 3) {
//            $this->outputException(t("To ensure your account safety, we currently accept THREE additional bank accounts daily. Please try again tomorrow."));
        }
    }

    /**
     * 判读ifsc是否有效
     * @param $ifsc
     * @return mixed
     * @throws \Common\Exceptions\ApiException
     */
    public function checkIfsc($ifsc)
    {
        $bankInfo = BankInfo::query()->where('ifsc', $ifsc)->first();
        if (!$bankInfo) {
            $this->outputException(t('ifsc不正确，请重新选择或填写'));
        }
        return $bankInfo;
    }

    public function updateRejectCount(BankCard $bankCard)
    {
        # 失败更新状态，无需计数3次拒绝90天
        return $bankCard->updateIsReject();
        # 失败计数到拒绝
        $days = 0;
        if ($bankCard->rejectCount($bankCard->user_id, self::$rejectDay) >= 3) {
            $days = self::$rejectDay;
        }
        $bankCard->updateIsReject($days);
        return $days;
    }

    /**
     * @param User $user
     * @param $cardNo
     * @param $ifsc
     * @return BankCardServer|void
     * @throws \Common\Exceptions\ApiException
     */
    public function initBankCard($params) {
        $user = \Auth::user();
        $params['user_id'] = $user->id;
        /** 防止银行卡并发请求  redis原子锁3秒 */
        if (!LockRedisHelper::helper()->userBankCardBind($user->id)) {
            $this->outputException('your bank account for verification.');
        }

        // 入库
        $model = Bankcard::model(Bankcard::SCENARIO_CREATE)->saveModel($params);
        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_BANKCARD);
        return $this->outputSuccess(t('银行卡绑定成功'), $model);
    }

}
