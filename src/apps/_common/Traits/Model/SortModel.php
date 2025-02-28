<?php

namespace Common\Traits\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Trait SortModel
 * @package Common\Traits\Model
 * @phan-file-suppress PhanUndeclaredMethod
 */
trait SortModel
{
    protected $sortKey = 'sort';

    protected $sortDirection = 'desc';

    protected $sortField;

    protected $sortCustom = [];

    /**
     *
     * @param Builder $query
     * @param $defaultSort
     *
     * @return mixed
     */
    public function scopeOrderByCustom($query, $defaultSort = null)
    {
        $this->initSortField($defaultSort);

        if (is_null($this->sortField) || !$this->sortJoin($query)) {
            return $query->orderBy($this->getKeyName(), 'desc');
        }

        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    protected function initSortField($defaultSort = null)
    {
        $sortKey = request($this->getSortKey());
        if (is_null($sortKey)) {
            $sortKey = $defaultSort;
        }

        if (!$sortKey) {
            return null;
        }

        if (starts_with($sortKey, '-')) {
            $this->sortDirection = 'desc';
            $sortKey = str_after($sortKey, '-');
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortCustom = $sortCustom = array_get($this->sortCustom(), $sortKey);

        $this->sortField = $sortCustom ? array_get($sortCustom, 'field') : null;
    }

    public function getSortKey()
    {
        return $this->sortKey;
    }

    public function setSortKey($sortKey)
    {
        $this->sortKey = $sortKey;
        return $this;
    }

    /**
     *
     * @param Builder $query
     * @return null
     */
    protected function sortJoin($query)
    {
        $sortCustom = $this->sortCustom;

        // related 为null 表示排序字段为自身表字段，不需进行 leftJoin
        $related = array_get($sortCustom, 'related');

        if (is_null($related)) {
            return true;
        }

        $this->sortJoinRelated($query, explode('.', $related));

        return true;
    }

    /**
     *
     * @param Builder $query
     * @param array $related
     * @param $currentModel
     *
     * @return mixed
     */
    private function sortJoinRelated($query, array $related, $currentModel = null)
    {
        if (!$related) {
            $this->sortField = optional($currentModel)->getTable() . '.' . $this->sortField;
            return $query;
        }

        $currentModel = $currentModel ?? new static();
        $currentRelated = array_shift($related);
        try {
            $currentRelated = optional($currentModel)->$currentRelated();
        } catch (\BadMethodCallException $e) {
            throw new \BadMethodCallException(get_class($currentModel) . '.' . $currentRelated . ' 关联关系不存在');
        }
        $nextModel = $currentRelated->getRelated();
        $currentTable = $currentModel->getTable();
        $nextTable = $nextModel->getTable();

        $currentTableKeyName = '';
        $nextTableKeyName = '';
        if ($currentRelated instanceof HasOne || $currentRelated instanceof HasMany) {
            $currentTableKeyName = $currentRelated->getQualifiedParentKeyName();
            $nextTableKeyName = $currentRelated->getQualifiedForeignKeyName();
        } elseif ($currentRelated instanceof BelongsTo) {
            $currentTableKeyName = $currentTable . '.' . $currentRelated->getForeignKey();
            $nextTableKeyName = $nextTable . '.' . $currentRelated->getOwnerKey();
        }

        $query->leftJoin($nextTable, $currentTableKeyName, '=', $nextTableKeyName);

        return $this->sortJoinRelated($query, $related, $nextModel);
    }

    /**
     * @return array
     */
    public function sortCustom()
    {
        return [];
    }
}
