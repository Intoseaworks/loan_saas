<?php

namespace Common\Models\Statistics;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class StatisticsCallRecording extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'statistics_call_recording';

}
