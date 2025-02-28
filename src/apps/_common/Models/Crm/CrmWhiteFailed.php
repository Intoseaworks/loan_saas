<?php

/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/1/29
 * Time: 16:08
 */

namespace Common\Models\Crm;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Crm\CrmWhiteFailed
 *
 * @property int $id
 * @property string|null $type 导入类型（WHITE_LIST，MARKETING）
 * @property int|null $batch_id 批次号
 * @property string|null $failed_data 失败数据
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteFailed newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteFailed newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteFailed orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteFailed query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteFailed whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteFailed whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteFailed whereFailedData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteFailed whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteFailed whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteFailed whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CrmWhiteFailed extends Model {

    use StaticModel;
    
    const TYPE_WHITE_LIST = "WHITE_LIST";
    const TYPE_MARKETING = "MARKETING";

    protected $table = 'crm_white_import_failed';


}
