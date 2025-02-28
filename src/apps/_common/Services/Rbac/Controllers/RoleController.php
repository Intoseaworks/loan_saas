<?php

namespace Common\Services\Rbac\Controllers;

use Common\Services\Rbac\Models\Role;
use Common\Services\Rbac\Rules\RoleRule;
use Common\Services\Rbac\Utils\Helper;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Exceptions\RoleAlreadyExists;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class RoleController extends BaseController
{
    /**
     * @var Role
     */
    private $role;

    public function __construct(RoleContract $role, Request $request)
    {
        $this->role = $role;
        $this->request = $request;
    }

    /**
     * 角色列表
     *
     * @return mixed
     */
    public function index()
    {
        $perPage = $this->request->input('per_page', 20);
        $roleName = $this->request->input('name');
        $roles = $this->role->list($perPage, $roleName);
        foreach ($roles as $role) {
            $role->name = t($role->name, 'rbac');
            $role->remark = t($role->remark, 'rbac');
        }
        return $this->resultSuccess($roles);
    }

    /**
     * @param RoleRule $rule
     * @return array
     * @throws Exception
     */
    public function show(RoleRule $rule)
    {
        if (!$rule->validate(RoleRule::SENARIO_SHOW, $this->request->all())) {
            return $this->resultFail($rule->getErrors());
        }

        try {
            $role = $this->role->findWithMenus($this->request->input('id'));
            return $this->resultSuccess($role);
        } catch (RoleDoesNotExist $exception) {
            return $this->resultFail('角色不存在');
        } catch (Exception $exception) {
            return $this->errorOutput($exception);
        }

    }

    /**
     * @param RoleRule $rule
     * @return array
     * @throws Exception
     */
    public function create(RoleRule $rule)
    {
        if (!$rule->validate(RoleRule::SENARIO_CREATE, $this->request->all())) {
            return $this->resultFail($rule->getErrors());
        }

        try {
            $resul = $this->role->add($this->request->all());
            return $this->resultSuccess($resul);
        } catch (RoleAlreadyExists $exception) {
            return $this->resultFail('权限已经存在');
        } catch (Exception $exception) {
            return $this->errorOutput($exception);
        }

    }

    /**
     * @param RoleRule $rule
     * @return array
     * @throws Exception
     */
    public function destory(RoleRule $rule)
    {
        if (!$rule->validate(RoleRule::SENARIO_DESTORY, $this->request->all())) {
            return $this->resultFail($rule->getErrors());
        }

        try {
            $this->role->destory($this->request->input('id'));
            return $this->resultSuccess();
        } catch (RoleDoesNotExist $exception) {
            return $this->resultFail('角色不存在');
        } catch (Exception $exception) {
            return $this->errorOutput($exception);
        }

    }

    /**
     * @param RoleRule $rule
     * @return array
     * @throws Exception
     */
    public function edit(RoleRule $rule)
    {
        if (!$rule->validate(RoleRule::SENARIO_EDIT, $this->request->all())) {
            return $this->resultFail($rule->getErrors());
        }

        try {
            $this->role->edit($this->request->all());
            return $this->resultSuccess();
        } catch (RoleDoesNotExist $exception) {
            return $this->resultFail('角色不存在');
        } catch (Exception $exception) {
            return $this->errorOutput($exception);
        }

    }

    /**
     * 获取所有角色
     *
     * @return Role[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllRole()
    {
        $roles = Role::all();
        foreach ($roles as $role) {
            $role->name = t($role->name, 'rbac');
        }
        return $this->resultSuccess($roles);
    }

    /**
     * 获取特定角色
     *
     * @return Role[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getSpecialRole()
    {
        $roles = Role::where('name','催收Collection')->get();
        foreach ($roles as $role) {
            $role->name = t($role->name, 'rbac');
        }
        return $this->resultSuccess($roles);
    }

    /**
     * 根据角色获取权限
     *
     * @param RoleRule $rule
     * @return array
     * @throws Exception
     */
    public function getPermissionByRole(RoleRule $rule)
    {
        if (!$rule->validate(RoleRule::SENARIO_ROLE_HAS_PERMISSION, $this->request->all())) {
            return $this->resultFail($rule->getErrors());
        }

        try {
            $roleIds = explode(',', $this->request->input('role_ids'));
            $permissions = $this->role->findWithPermissionByRole($roleIds);
            return $this->resultSuccess($permissions);
        } catch (RoleDoesNotExist $exception) {
            return $this->resultFail('角色不存在');
        } catch (Exception $exception) {
            return $this->errorOutput($exception);
        }
    }

    /**
     * 获取授权用户列表
     *
     * @return array
     */
    public function getRoleUser()
    {
        $perPage = $this->request->input('per_page', 20);
        $roleIds = $this->request->input('role_ids');
        $userName = $this->request->input('username');
        if ($roleIds) {
            $roleIds = explode(',', $roleIds);
        }
        return $this->resultSuccess($this->role->roleUserList($perPage, $roleIds, $userName));
    }

    /**
     * 用户权限赋值
     *
     * @param RoleRule $rule
     * @return array
     * @throws Exception
     */
    public function assignRole(RoleRule $rule)
    {
        if (!$rule->validate(RoleRule::SENARIO_ASSIGN_ROLE, $this->request->all())) {
            return $this->resultFail($rule->getErrors());
        }
        try {
            $roleIds = $this->request->input('role_ids');
            $uid = $this->request->input('uid');
            if ($roleIds) {
                return $this->resultSuccess($this->role->assignRole($uid, $roleIds, true));
            } else {
                //清空所有角色
                return $this->resultSuccess($this->role->detachRole($uid));
            }
        } catch (Exception $exception) {
            return $this->errorOutput($exception);
        }
    }

    /**
     * 用户权限清空
     *
     * @param RoleRule $rule
     * @return array
     * @throws Exception
     */
    public function detachRole(RoleRule $rule)
    {
        if (!$rule->validate(RoleRule::SENARIO_DETACH_ROLE, $this->request->all())) {
            return $this->resultFail($rule->getErrors());
        }
        try {
            return $this->resultSuccess($this->role->detachRole($this->request->input('uid')));
        } catch (Exception $exception) {
            return $this->errorOutput($exception);
        }
    }

    /**
     * 未授权角色的人员列表
     *
     * @param RoleRule $rule
     * @return array
     */
    public function getUnauthorizedUser(RoleRule $rule)
    {
        if (!$rule->validate(RoleRule::SENARIO_UNAUTHORIZED_USER, $this->request->all())) {
            return $this->resultFail($rule->getErrors());
        }
        $roleId = $this->request->input('role_id');
        $userName = $this->request->input('username');
        return $this->resultSuccess($this->role->unauthorizedUserList($roleId, $userName));
    }

    /**
     * 返回用户角色
     *
     * @param RoleRule $rule
     * @return array
     * @throws Exception
     */
    public function getUserRole(RoleRule $rule)
    {
        if (!$rule->validate(RoleRule::SENARIO_USER_ROLE, $this->request->all())) {
            return $this->resultFail($rule->getErrors());
        }
        $uid = $this->request->input('uid');
        try {
            return $this->resultSuccess($this->role->userRoleList($uid));
        } catch (\Exception $exception) {
            return $this->errorOutput($exception);
        }
    }

    /**
     * 返回模块列表
     *
     * @return array
     */
    public function getGuardList()
    {
        return $this->resultSuccess(Helper::getGuardList());
    }
}
