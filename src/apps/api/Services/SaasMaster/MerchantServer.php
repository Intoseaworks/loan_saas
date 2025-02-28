<?php

namespace Api\Services\SaasMaster;

use Api\Services\BaseService;
use Common\Models\Merchant\Merchant;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MerchantServer extends BaseService
{
    public function initMerchant($params)
    {
        $merchant = Merchant::model()->getNormalById($params['merchantId']);
        $username = $params['adminUsername'];
        $password = $params['adminPassword'];

        if (!$merchant) {
            return $this->outputError('商户不存在或状态不正确');
        }

        MerchantHelper::setMerchantId($merchant->id);

        if ($merchant->superAdmin) {
            return $this->outputError('商户已有初始化账户，请检查后重试');
        }

        DB::beginTransaction();
        try {
            ob_start();
            Artisan::call('init:config', [
                'config' => 'all',
                '--merchantId' => $merchant->id,
                '--adminUsername' => $username,
                '--adminPassword' => $password,
            ]);
            ob_end_clean();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            DingHelper::notice(json_encode($params), 'saas 商户初始化失败', DingHelper::AT_SOLIANG);
            return $this->outputError('商户初始化失败，请重试');
        }

        return $this->outputSuccess('商户初始化成功');
    }

    public function createInitMerchant($params)
    {
        $merchantId = array_get($params, 'merchantId');
        $merchant = Merchant::model()->getNormalById($merchantId);
        $username = array_get($params, 'adminUsername');
        $password = array_get($params, 'adminPassword');

        if ($merchant) {
            return $this->outputError('商户ID已存在' . $merchant->id);
        }

        DB::beginTransaction();
        try {
            $params['id'] = $merchantId;
            $params['app_key'] = array_get($params, 'merchant_app_key');
            $params['app_secret_key'] = array_get($params, 'merchant_app_secret_key');
            $merchant = Merchant::model()->add($params);

            MerchantHelper::setMerchantId($merchant->id);
            ob_start();
            Artisan::call('init:config', [
                'config' => 'all',
                '--merchantId' => $merchant->id,
                '--adminUsername' => $username,
                '--adminPassword' => $password,
            ]);
            ob_end_clean();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            DingHelper::notice(json_encode($params), 'saas 商户初始化失败', DingHelper::AT_SOLIANG);
            return $this->outputError('商户初始化失败，请重试-' . $e->getMessage().'文件:'.$e->getFile().'行:'.$e->getLine());
        }

        return $this->outputSuccess('商户初始化成功');
    }

    /**
     * 修改商户超管密码
     * @param $merchantId
     * @param $password
     * @return MerchantServer
     */
    public function updPassword($merchantId, $password)
    {
        $merchant = Merchant::find($merchantId);

        if (!$merchant || !$superAdmin = $merchant->superAdmin) {
            return $this->outputError('商户不存在获取未初始化超管账号');
        }

        if (!$superAdmin->updPassword($password)) {
            return $this->outputError('修改失败');
        }

        return $this->outputSuccess('修改成功');
    }

    /**
     * 获取商户超管账号信息
     * @param $merchantId
     * @return MerchantServer
     */
    public function getSuperAdmin($merchantId)
    {
        $merchants = Merchant::whereIn('id', (array)$merchantId)->get();

        if ($merchants->isEmpty()) {
            return $this->outputError('商户不存在');
        }

        $result = [];
        foreach ($merchants as $merchant) {
            $result[$merchant->id] = $merchant->superAdmin;
        }

        return $this->outputSuccess('获取成功', $result);
    }
}
