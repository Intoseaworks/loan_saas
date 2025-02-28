<?php

namespace Risk\Common\Models;

use Common\Utils\Data\ArrayHelper;

trait BatchSave
{
    /**
     * @param        $data
     * @param array $filed
     * @param string $insertSql
     * @return mixed
     * @throws \Exception
     */
    protected function batchInsert($data, array $filed = null, $insertSql = 'REPLACE INTO ')
    {
        $data = ArrayHelper::convertTwoDimensional($data);
        $table = $this->getTable();

        foreach ($data as $item) {
            if (!is_array($item)) {
                throw new \Exception('data 数据异常');
            }
        }

        if ($filed == null) {
            $filed = $this->fillable;
        }

        if (!$filed) {
            return false;
        }

        foreach ($filed as $key => $val) {
            if (!$this->isAssign($val)) {
                unset($filed[$key]);
            }
        }
        $filedStr = implode('`,`', $filed);
        $filedStr = "(`{$filedStr}`)";

        $values = [];
        $bindings = [];
        foreach ($data as $val) {
            $qs = [];
            foreach ($filed as $k) {
                $bindings[] = array_get($val, $k);
                $qs[] = '?';
            }
            $values[] = '(' . implode(",", $qs) . ')';
        }

        $valuesStr = implode(",", $values);

        $sql = "{$insertSql} `{$table}` $filedStr VALUES $valuesStr";
        $bool = $this->getConnection()->insert($sql, $bindings);
        return $bool;
    }


    public function isAssign($field)
    {
        if (empty($this->fillable)) {
            return true;
        }

        return in_array($field, $this->fillable);
    }


}
