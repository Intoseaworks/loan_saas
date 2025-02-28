<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-11-27
 * Time: 17:00
 */

namespace Common\Traits;

use DB;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;

/**
 * Trait DbExtension
 * @package Common\Traits
 * @phan-file-suppress PhanUndeclaredStaticMethod
 * @phan-file-suppress PhanUndeclaredMethod
 */
trait DbExtension
{

    /**
     * @param array $update
     * @param string $whenField
     * @param string $whereField
     * @suppress PhanUndeclaredMethod
     */
    public function batchUpdate(array $update, $whenField = 'id', $whereField = 'id')
    {
        $table = $this->getTable();
        $update = collect($update);
        // 判断需要更新的数据里包含有放入when中的字段和where的字段
        if ($update->pluck($whenField)->isEmpty() || $update->pluck($whereField)->isEmpty()) {
            throw new \InvalidArgumentException('argument 1 don\'t have field ' . $whenField);
        }
        $when = [];
        // 拼装sql，相同字段根据不同条件更新不同数据
        foreach ($update->all() as $sets) {
            $whenValue = $sets[$whenField];
            foreach ($sets as $fieldName => $value) {
                if ($fieldName == $whenField) continue;
                if (is_null($value)) $value = 'null';
                $when[$fieldName][] = "when {$whenField} = '{$whenValue}' then '{$value}'";
            }
        }

        $build = DB::table($table)->whereIn($whereField, $update->pluck($whereField));
        if ($connection = $this->getConnectionName()) {
            $build->connection($connection);
        }

        foreach ($when as $fieldName => &$item) {
            $item = DB::raw("case " . implode(' ', $item) . ' end ');
        }

        $build->update($when);

    }

    /**
     * @return mixed
     * @suppress PhanUndeclaredMethod
     */
    public function getDatabaseName()
    {
        $connection = $this->getConnectionName() ?: 'mysql';
        return config("database.connections.{$connection}.database");
    }

    /**
     * @param array $values
     * @return bool
     */
    public function replaceInto(array $values)
    {
        if (empty($values)) {
            return true;
        }
        $data = $this->buildInsertSql($values);
        $replaceSql = str_replace_first('insert', 'replace', $data['sql']);
        return static::query()->getConnection()->statement($replaceSql, $data['bindings']);
    }

    /**
     * @param array $values
     * @return bool
     */
    public function insertIgnore(array $values)
    {
        if (empty($values)) {
            return true;
        }
        $data = $this->buildInsertSql($values);
        $replaceSql = str_replace_first('insert', 'insert ignore ', $data['sql']);
        return static::query()->getConnection()->statement($replaceSql, $data['bindings']);
    }

    /**
     * @param array $bindings
     * @return array
     */
    protected function cleanBindings(array $bindings)
    {
        return array_values(array_filter($bindings, function ($binding) {
            return !$binding instanceof Expression;
        }));
    }

    /**
     *
     * @param $values
     * @return array
     */
    protected function buildInsertSql($values)
    {
        if (!is_array(reset($values))) {
            $values = [$values];
        } else {
            foreach ($values as $key => $value) {
                ksort($value);

                $values[$key] = $value;
            }
        }

        /** @var \Illuminate\Database\Query\Builder $query */
        $query = static::query()->getQuery();
        $bindings = $this->cleanBindings(Arr::flatten($values, 1));
        return ['sql' => $query->grammar->compileInsert($query, $values), 'bindings' => $bindings];
    }
}
