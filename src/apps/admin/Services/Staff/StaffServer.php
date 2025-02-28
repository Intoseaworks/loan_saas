<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Staff;

use Admin\Models\Staff\Staff;
use Admin\Services\BaseService;
use Admin\Services\Ticket\TicketServer;
use Common\Services\Rbac\Models\Role;
use Common\Utils\Data\DateHelper;
use Common\Utils\LoginHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Common\Models\Collection\CollectionAdmin;
use Common\Utils\MerchantHelper;
use Common\Utils\Host\HostHelper;

class StaffServer extends BaseService {

    /**
     * 获取用户列表
     * @param $param
     * @return mixed
     */
    public static function getUserList($param) {
        $data = Staff::model()->getList($param);
        foreach ($data as &$item) {
            if ($item->roles) {
                foreach ($item->roles as $role) {
                    $role->name = t($role->name, 'rbac');
                }
            }
            $levels = [];
            $colloction = CollectionAdmin::model()->where(['admin_id' => $item->id, 'status' => '1'])->get();
            if (count($colloction)) {
                $colloction = $colloction->toArray();
                foreach ($colloction as $col) {
                    $levels[] = $col['level_name'];
                }
                $item->levels = $levels;
                $item->language = $colloction[0]['language'];
            }
        }
        return $data;
    }

    /**
     * 获取特定角色用户列表
     * @param $param
     * @return mixed
     */
    public static function getSpecialUserList($param) {
        $data = Staff::model()->getSpecialList($param);
        foreach ($data as &$item) {
            if ($item->roles) {
                foreach ($item->roles as $role) {
                    $role->name = t($role->name, 'rbac');
                }
            }
            $levels = [];
            $colloction = CollectionAdmin::model()->where(['admin_id' => $item->id, 'status' => '1'])->get();
            if (count($colloction)) {
                $colloction = $colloction->toArray();
                foreach ($colloction as $col) {
                    $levels[] = $col['level_name'];
                }
                $item->levels = $levels;
                $item->language = $colloction[0]['language'];
            }
        }
        return $data;
    }

    /**
     * 创建用户
     * @param $data
     * @return bool|mixed
     */
    public static function createUser($data) {
        $data['password'] = Staff::passwordHash(trim($data['password']));
        $data['last_update_pwd_time'] = DateHelper::dateTime();
        $modelStaff = Staff::model(Staff::SCENARIO_CREATE)->saveModel($data);

        self::saveLevels($modelStaff->id, $data);
        return $modelStaff;
    }

    public static function saveLevels($staffId, $data) {
        if (isset($data['levels']) && $data['levels']) {
            $data['language'] = isset($data['language']) ? $data['language'] : 'All';
            $levels = explode(',', $data['levels']);
            CollectionAdmin::model()->where(["admin_id" => $staffId])->whereNotIn("level_name", $levels)->update(["status" => "2"]);
            CollectionAdmin::model()->where(["admin_id" => $staffId])->whereIn("level_name", $levels)->update(["language" => $data['language']]);
            foreach ($levels as $level) {
                if (!CollectionAdmin::model()->where(["admin_id" => $staffId])->where('level_name', $level)->where("status", 1)->exists()) {
                    $attributes['merchant_id'] = MerchantHelper::getMerchantId();
                    $attributes['admin_id'] = $staffId;
                    $attributes['level_name'] = $level;
                    $attributes['language'] = $data['language'];
                    CollectionAdmin::model()->createModel($attributes);
                }
            }
        } else {
            CollectionAdmin::model()->where(["admin_id" => $staffId])->update(["status" => "2"]);
        }
    }

    /**
     * 修改用户
     * @param Staff $model
     * @param Request $request
     * @return bool|mixed
     */
    public static function updateUser(Staff $model, Request $request) {
        $data = $request->except('password_hash');
        $updPwd = 0;
        if (isset($data['password']) && $data['password'] != null && $data['password'] != $model->password) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => 13]);
            $data['last_update_pwd_time'] = DateHelper::dateTime();
            $updPwd = 1;
        }
        $model->setScenario(Staff::SCENARIO_UPDATE);
        $result = $model->saveModel($data);
        if ($result) {
            //禁用和修改密码 删除缓存
            if ((isset($data['status']) && $data['status'] == 0) || $updPwd === 1) {
                TicketServer::server()->delUserTicket($data['id']);
            }
        }
        return $result;
    }

    /**
     * 查找用户
     * @param $id
     * @return Staff
     */
    public static function findOneById($id) {
        return Staff::model()->getOne($id);
    }

    /**
     * @param $id
     * @param $username
     * @return mixed
     */
    public static function getOneByIdAndUsername($id, $username) {
        $data = [
            'id' => $id,
            'username' => $username,
        ];
        return Staff::model()->getOneByData($data);
    }

    /**
     * 删除用户
     * @param $id
     * @return mixed
     */
    public static function delete($id) {
        TicketServer::server()->delUserTicket($id);
        return Staff::model()->deleteById($id);
    }

    /**
     * 禁用/启用
     * @param $id
     * @param $status
     * @return mixed
     */
    public static function disableOrEnable($id, $status) {
        TicketServer::server()->delUserTicket($id);
        return Staff::model()->updateStatusById($id, $status);
    }

    /**
     * 设置密码
     * @param $id
     * @param $password
     * @param bool $unbindDing
     * @return mixed
     */
    public static function passwordSetting($id, $password, $unbindDing = false) {
        $update = [];
        $password && $update['password'] = Staff::passwordHash($password);
        $unbindDing && $update['ding_unionid'] = '';
        return Staff::where('id', $id)->update($update);
    }

    /**
     * 初始化用户
     * @param $id
     * @return bool|int
     */
    public static function userInitialization($id) {
        TicketServer::server()->delUserTicket($id);
        return Staff::model()->initialization($id);
    }

    /**
     * 重置密码
     * @param $id
     * @return mixed
     */
    public static function userReset($id) {
        TicketServer::server()->delUserTicket($id);
        return Staff::model()->reset($id);
    }

    /**
     * 更新用户登录信息
     * @param $id
     * @param $ip
     * @return mixed
     */
    public static function updateLoginInfo($id, $ip) {
        $data = [
            'last_login_time' => DateHelper::dateTime(),
            'last_login_ip' => $ip,
            'updated_at' => time(),
        ];
        return Staff::model()->updateById($id, $data);
    }

    /**
     * 绑定钉钉
     *
     * @param false|string $id
     * @param string $dingUnionId
     *
     * @return mixed
     */
    public static function saveDingId($id, $dingOpenId, $dingUnionId) {
        $data = [
            'ding_openid' => $dingOpenId,
            'ding_unionid' => $dingUnionId,
            'updated_at' => time(),
        ];
        return Staff::model()->updateById($id, $data);
    }

    public static function updateById($id, $update) {
        return Staff::where('id', $id)->update($update);
    }

    /**
     * 赋值角色
     * @param $uid
     * @param $roleIds
     * @return bool
     */
    public static function assignRole($uid, $roleIds) {
        // 赋值角色
        try {
            $role = new Role();
            if ($roleIds) {
                return $role->assignRole($uid, $roleIds, true);
            } else {
                //清空所有角色
                return $role->detachRole($uid);
            }
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function checkPassword($password) {
        $adminId = LoginHelper::getAdminId();
        $staff = Staff::model()->getOne($adminId);
        if (!Hash::check($password, $staff->password)) {
            return false;
        }
        return true;
    }

    public function editPassword($params) {
        $oldPassword = array_get($params, 'old_password');
        $newPassword = array_get($params, 'new_password');
        $againPassword = array_get($params, 'again_password');
        if ($newPassword != $againPassword) {
            return $this->resultFail('两次密码不一致');
        }
        $adminId = LoginHelper::getAdminId();
        $staff = Staff::model()->getOne($adminId);
        if (!Hash::check($oldPassword, $staff->password)) {
            return $this->outputException('原密码有误');
        }
        if (!StaffServer::passwordSetting($staff->id, $newPassword)) {
            return $this->outputException('账号密码设置失败');
        }
        return $this->outputSuccess(t('账号密码设置成功', 'staff'));
    }

    public function getByIds($adminIds, $isActive = true) {
        $model = Staff::model();

        if ($isActive) {
            $model = $model->isActive();
        }

        return $model->whereIn('id', $adminIds)->get();
    }

    /**
     * 根据id查找所有staff ，包括已禁用和已删除
     * @param $adminIds
     * @return Staff[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllByIds($adminIds) {
        return Staff::query()->withoutGlobalScope('valid')
                        ->whereIn('id', $adminIds)
                        ->get();
    }

    public function checkInRoles(array $checkRoles) {
        $roles = \Common\Utils\LoginHelper::$modelUser->roles;
        foreach ($roles as $role) {
            //echo $role->name.PHP_EOL;
            if (in_array($role->name, $checkRoles)) {
                return true;
            }
        }
        return false;
    }

    public function statistics($params) {
        $pageSize = array_get($params, 'size', 10);
        $currentPage = array_get($params, 'page', 1);
        $merchantId = MerchantHelper::getMerchantId();
        $limit = " LIMIT " . (($currentPage - 1) * $pageSize) . ", {$pageSize};";
        $where = "";
        if (isset($params['admin_id'])) {
            $where .= " AND stf.id='{$params['admin_id']}' ";
        }
        if (isset($params['username'])) {
            $where .= " AND stf.username='{$params['username']}' ";
        }
        if (isset($params['login_time']) && is_array($params['login_time'])) {
            $where .= " AND (log.created_at>'{$params['login_time'][0]}' AND log.created_at<'{$params['login_time'][1]}') ";
        }
        if (isset($params['role_id']) && is_array($params['role_id'])) {
            $where .= " AND stf.id IN (select model_id from rbac_model_has_roles
WHERE `role_id` in ('" . implode("','", $params['role_id']) . "')) ";
        }
        if (isset($params['type']) && $params['type'] == 'detail') {
            $sql = "select 
stf.id as staff_id,
stf.username,
stf.nickname,
'' as role,
log.created_at online_time,
log.offline_time,
log.status_value,
date(log.created_at) as date,
ip
from collection_online_log log
INNER JOIN staff stf ON stf.id=log.admin_id
WHERE log.`status`=1 AND stf.merchant_id={$merchantId} {$where}
ORDER BY log.id desc
";
        } else {
            $sql = "select 
stf.id as staff_id,
stf.username,
stf.nickname,
'' as role,
max(offline_time) as offline_time,
min(log.created_at) as online_time,
log.status_value,
date(log.created_at) as date,
ip
from collection_online_log log
INNER JOIN staff stf ON stf.id=log.admin_id
WHERE log.`status`=1 AND stf.merchant_id={$merchantId} {$where}
GROUP BY staff_id,date
ORDER BY log.id desc,log.admin_id desc";
        }
        $res = \DB::select($sql);
        $data = [];
        $data['total'] = count($res);
        $data['list'] = \DB::select($sql . $limit);
        foreach ($data['list'] as $item) {
            $staff = Staff::model()->getOne($item->staff_id);
            if ($staff && $staff->roles) {
                foreach ($staff->roles as $role) {
                    $item->role .= t($role->name, 'rbac') . " ";
                }
            }
            //$item->ip .= "/" . HostHelper::getAddressByIp($item->ip);
        }
        $data['page_total'] = ceil($data['total'] / $pageSize);
        $data['current_page'] = $currentPage;
        $data['page_size'] = $pageSize;
        return $data;
    }

}
