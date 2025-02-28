<?php

namespace Common\Traits\Model;

trait ExportModel
{
    /**
     * 导出设置分页大小
     * @param int $pageSize
     * @return mixed
     */
    public function exportPageSize($pageSize = 1000)
    {
        // @phan-suppress-next-line PhanUndeclaredMethod
        return $this->setPerPage($pageSize);
    }
}
