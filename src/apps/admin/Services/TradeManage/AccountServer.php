<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/28
 * Time: 10:30
 */

namespace Admin\Services\TradeManage;


use Admin\Services\BaseService;
use Common\Models\Trade\AdminTradeAccount as Account;
use Common\Services\Bankcard\BankcardServer;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Data\ValidtionHelp;
use Illuminate\Support\Facades\DB;

class AccountServer extends BaseService
{
    /**
     * 账号列表
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list($params)
    {
        $size = array_get($params, 'size');
        $accounts = Account::model()->search($params)->paginate($size);
        //@phan-suppress-next-line PhanTypeNoPropertiesForeach
        foreach ($accounts as $account) {
            /** @var $account Account */
            $account->setScenario($account::SCENE_LIST)->getText();
        }
        return $accounts;
    }

    /**
     * 添加账户
     * @param $params
     * @throws \Common\Exceptions\ApiException
     */
    public function create($params)
    {
        $paymentMethod = array_get($params, 'payment_method');
        $accountNo = array_get($params, 'account_no');
        if ($accountNo && $paymentMethod == Account::PAYMENT_METHOD_BANK) {
            if (!ValidtionHelp::validBankCardNo($accountNo)) {
                return $this->outputException('银行卡格式不正确');
            }
            $params['bank_name'] = array_get(BankcardServer::server()->bin($accountNo), 'bankname');
        }
        /** 同一业务类型、支付方式，账户需去重判断 */
        $this->hasExist($params);
        DB::beginTransaction();
        /** 保证入款激活中唯一账号 */
        $type = array_get($params, 'type');
        if ($type == Account::TYPE_OUT) {
            $this->clearByType($type);
        }
        if (!Account::model()->store($params)) {
            DB::rollBack();
            return $this->outputException('创建失败');
        }
        DB::commit();
    }

    /**
     * 同一业务类型、支付方式，账户需去重判断
     * @param $params
     * @throws \Common\Exceptions\ApiException
     */
    public function hasExist($params)
    {
        $type = array_get($params, 'type');
        $paymentMethod = array_get($params, 'payment_method');
        $accountNo = array_get($params, 'account_no');
        if ($accountNo && Account::whereType($type)->wherePaymentMethod($paymentMethod)->whereAccountNo($accountNo)->exists()) {
            return $this->outputException('账户已存在，请重新设置');
        }
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return void
     * @throws \Common\Exceptions\ApiException
     */
    public function changeStatus($id, $status)
    {
        $account = Account::model()->getOne($id);
        DB::beginTransaction();
        switch ($status) {
            case Account::STATUS_ACTIVE:
                if ($account->type == Account::TYPE_OUT) {
                    /** 保证出款激活中唯一账号 */
                    $this->clearByType($account->type);
                }
                if (!$account->enable()) {
                    DB::rollBack();
                    return $this->outputException('更新失败');
                }
                break;
            case Account::STATUS_DELETE:
                /** 保证支付方式至少保留一种 */
                $this->isLastOneType($account->type);
                if (!$account->disable()) {
                    DB::rollBack();
                    return $this->outputException('更新失败');
                }
                break;
        }
        DB::commit();
    }

    public function changeDefault($id)
    {
        $account = Account::model()->getOne($id);
        if (!$account) {
            return $this->outputException('出款方式不存在');
        }
        if ($account->status == Account::STATUS_DELETE) {
            return $this->outputException('禁用账户不能设置默认');
        }
        if ($account->type != Account::TYPE_IN) {
            return $this->outputException('仅出款支持设置默认');
        }
        DB::beginTransaction();
        if ($account->is_default == Account::IS_DEFAULT) {
            return $this->outputException('该出款方式已为默认');
        }
        Account::model()->disableDefaultAll();
        if (!$account->enableDefault()) {
            DB::rollBack();
            return $this->outputException('更新失败');
        }
        DB::commit();
    }

    public function isHasDefault()
    {
        if (Account::model()->active()->where('is_default', Account::IS_DEFAULT)->count() > 0) {
            return $this->outputException('已存在默认支付方式');
        }
    }

    /**
     * 清理出/入款账号 禁用
     * @param $type
     */
    public function clearByType($type)
    {
        foreach (Account::model()->active()->whereType($type)->get() as $item) {
            $item->disable();
        }
    }

    /**
     * 修改状态之前检查
     * 一种支付方式至少保留一种
     * @param $type
     * @throws \Common\Exceptions\ApiException
     */
    public function isLastOneType($type)
    {
        /** 一种支付方式至少保留一种 */
        if (Account::model()->active()->whereType($type)->count() == 1) {
            $typeMethodText = ts(array_get(Account::TYPE_ALIAS, (string)$type), 'pay');
            $text = "{$typeMethodText}账户禁用失败，请确保至少有一项正常使用出款/收款账户";
            return $this->outputException($text);
        }
    }

    /**
     * 判断后台支付账号能否进行人工放款
     * @param $tradeAccountId
     * @return mixed
     */
    public function canManualRemit($tradeAccountId)
    {
        $where = [
            'id' => $tradeAccountId,
            'type' => Account::TYPE_OUT,
            'status' => Account::STATUS_ACTIVE,
            'payment_method' => Account::PAYMENT_METHOD_BANK,
        ];
        return Account::where($where)->first();
    }

    /**
     * 获取有效的放款账号(银行卡)
     * @param bool $isOption
     * @return array
     */
    public function getPayAccount($isOption = false)
    {
        $condition = [
            'status' => Account::STATUS_ACTIVE,
            'type' => Account::TYPE_OUT,
            'payment_method' => Account::PAYMENT_METHOD_BANK,
        ];
        return $this->getAccount($condition, $isOption);
    }

    /**
     * 获取后台收款账号
     * @param bool $isOption
     * @return array
     */
    public function getRepayAccount($isOption = false)
    {
        $condition = [
            'type' => Account::TYPE_IN,
            'payment_method' => [
                Account::PAYMENT_METHOD_BANK,
//                Account::PAYMENT_METHOD_ALIPAY,
//                Account::PAYMENT_METHOD_WEIXIN
            ]
        ];
        return $this->getAccount($condition, $isOption);
    }

    /**
     * @param array $condition
     * @param bool $isOption
     * @return array|Account[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAccount($condition = [], $isOption = false)
    {
        $accounts = Account::model()->search($condition)->orderBy('status', 'desc')->get();
        foreach ($accounts as $account) {
            /** @var $account Account */
            $account->setScenario($account::SCENE_OPTION)->getText();
        }
        if ($isOption) {
            return ArrayHelper::arrayChangeKey($accounts->toArray(), 'id');
        }
        return $accounts->toArray();
    }
}
