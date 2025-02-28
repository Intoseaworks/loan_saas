<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Models\Third;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Third\ThirdDataFxorderLog
 *
 * @property int $id
 * @property string|null $event_name 接口事件名
 * @property string|null $json_data 接口请求数据
 * @property string|null $response_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataFxorderLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataFxorderLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataFxorderLog orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataFxorderLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataFxorderLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataFxorderLog whereEventName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataFxorderLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataFxorderLog whereJsonData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataFxorderLog whereResponseData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataFxorderLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ThirdDataFxorderLog extends Model {

    use StaticModel;

    protected $table = 'third_data_fxorder_log';
    protected $fillable = [];
    protected $guarded = [];

}
