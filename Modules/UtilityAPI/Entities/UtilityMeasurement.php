<?php

namespace Modules\UtilityAPI\Entities;
use Illuminate\Database\Eloquent\Model;

class UtilityMeasurement extends Model
{

    protected $fillable = [
        'id',
        'meter_id',
        'utilityapi_interval_uid',
        'start_at',
        'end_at',
        'kwh_value',
        'datapoints'
    ];

    protected $table = 'utilityapi_measurements';
}
