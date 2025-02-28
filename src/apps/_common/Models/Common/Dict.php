<?php

namespace Common\Models\Common;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;
use Common\Redis\CommonRedis;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Common\Models\Common\Dict
 *
 * @property int $dict_id
 * @property string|null $name 字典名称
 * @property string|null $code 字典编码
 * @property string|null $parent_code 字典父级编码
 * @property string|null $name_cn 字典中文描述
 * @property string|null $attr1 预留字段1
 * @property string|null $attr2 预留字段1
 * @property string|null $attr3 预留字段1
 * @property int|null $sort 排序
 * @property string|null $remark 备注
 * @property string|null $status 状态：Y/N
 * @property string|null $create_time
 * @property string|null $create_user
 * @property string|null $update_time
 * @property string|null $update_user
 * @property int|null $version
 * @method static \Illuminate\Database\Eloquent\Builder|Dict newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Dict newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Dict orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Dict query()
 * @method static \Illuminate\Database\Eloquent\Builder|Dict whereAttr1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dict whereAttr2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dict whereAttr3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dict whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dict whereCreateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dict whereCreateUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dict whereDictId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dict whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dict whereNameCn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dict whereParentCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dict whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dict whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dict whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dict whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dict whereUpdateUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dict whereVersion($value)
 * @mixin \Eloquent
 */
class Dict extends Model {

    use StaticModel;

    public $timestamps = false;
    protected $table = 'dictionary';
    protected $fillable = [];
    protected $hidden = [];
    protected $primaryKey = 'dict_id';
    protected $connection = 'mysql_readonly';

    public function getKeyVal($parent_code) {
        $query = self::query()->select(['code', 'name'])
                ->orderBy('sort');
        if ($parent_code) {
            $query->where(['parent_code' => $parent_code]);
        }
        return $query->pluck('name', 'code');
    }

    public static function getNameByCode($code) {
        $rds = CommonRedis::redis();
        $version = '-1.0'; # 如有更新累加
        $locale = app('translator')->getLocale();
        if ($locale == 'ar-EG') {
            $version .= '-eg';
        }
        $key = self::class . date("Y") . $code . $version;
        $value = $rds->get($key);
        if ($value) {
            return $value;
        }
        $col = 'name as name';
        if ($locale == 'ar-EG') {
            $col = 'name_local as name';
        }
        $query = self::query()->select(['code', $col])
                ->orderBy('sort');
        $query->where(['code' => $code]);
        $res = $query->first();
        if ($res) {
            $res = $res->toArray();
            $value = $res['name'];
        } else {
            $value = $code;
        }
        $rds->set($key, $value, 3600 * 24 * 30);
        return $value;
    }

    public function getList($where = []) {
        $locale = app('translator')->getLocale();
//        dd($locale);
        $select = ['dict_id', 'name', 'code', 'parent_code', 'create_time', 'update_time'];
        if ($locale == 'ar-EG') {
            $select = ['dict_id', 'name_local as name', 'code', 'parent_code', 'create_time', 'update_time'];
        }
        $query = self::query()
                        ->select($select)
                        ->where("status", "Y")
                        ->orderBy('sort')->orderBy("name");
        if ($where) {
            $query->where($where);
        }
        return $query->get();
    }

}
