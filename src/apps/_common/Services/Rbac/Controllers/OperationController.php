<?php

namespace Common\Services\Rbac\Controllers;

use Common\Services\Rbac\Exceptions\OperationAlreadyExists;
use Common\Services\Rbac\Exceptions\OperationDoesNotExist;
use Common\Services\Rbac\Models\Operation;
use Common\Services\Rbac\Rules\OperationRule;
use Common\Services\Rbac\Rules\RoleRule;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class OperationController extends BaseController
{

    /**
     * @var Operation
     */
    private $operation;

    public function __construct(Operation $operation, Request $request)
    {
        $this->operation = $operation;
        $this->request = $request;
    }

    /**
     * @param RoleRule $rule
     * @return array
     * @throws Exception
     */
    public function create(OperationRule $rule)
    {
        if (!$rule->validate(OperationRule::SENARIO_CREATE, $this->request->all())) {
            return $this->resultFail($rule->getErrors());
        }

        try {
            $resul = $this->operation->add($this->request->all());
            return $this->resultSuccess($resul);
        } catch (OperationAlreadyExists $exception) {
            return $this->resultFail($exception->getMessage());
        } catch (PermissionDoesNotExist $exception) {
            return $this->resultFail($exception->getMessage());
        } catch (Exception $exception) {
            return $this->errorOutput($exception);
        }

    }

    /**
     * @param RoleRule $rule
     * @return array
     * @throws Exception
     */
    public function destory(OperationRule $rule)
    {
        if (!$rule->validate(OperationRule::SENARIO_DESTORY, $this->request->all())) {
            return $this->resultFail($rule->getErrors());
        }

        try {
            $this->operation->destory($this->request->input('id'));
            return $this->resultSuccess();
        } catch (OperationDoesNotExist $exception) {
            return $this->resultFail($exception->getMessage());
        } catch (Exception $exception) {
            return $this->errorOutput($exception);
        }

    }

    /**
     * @param OperationRule $rule
     * @return array
     * @throws Exception
     */
    public function edit(OperationRule $rule)
    {
        if (!$rule->validate(OperationRule::SENARIO_EDIT, $this->request->all())) {
            return $this->resultFail($rule->getErrors());
        }

        try {
            $operation = $this->operation->edit($this->request->all());
            return $this->resultSuccess($operation);
        } catch (OperationDoesNotExist $exception) {
            return $this->resultFail($exception->getMessage());
        } catch (Exception $exception) {
            return $this->errorOutput($exception);
        }

    }

    /**
     * @return array
     */
    public function syncOperation()
    {
        $operations = $this->request->input('operations');
        if (!$operations) {
            return $this->resultFail('功能不存在');
        }
        //同步菜单
        $this->operation->syncOperation($operations);
        return $this->resultSuccess();
    }
}
