<?php

namespace Common\Models\Collection;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class CollectionOnlineLog extends Model {

    const STATUS_ONLINE = 1;
    const STATUS_OFFLINE = 2;
    const STATUS_VALUE = [
        "meeting",
        "training",
        "rest",
        "tea break",
        "off duty",
    ];

    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'collection_online_log';

}
