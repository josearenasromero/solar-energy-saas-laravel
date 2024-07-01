<?php

namespace Modules\RateAcuity\Entities;
use Illuminate\Database\Eloquent\Model;

class ScheduleDemandTime extends Model
{
    protected $fillable =[
        'schedule_id',
        'description',
        'rate_kw',
        'min_kv',
        'max_kv',
        'season',
        'start_date',
        'end_date',
        'time_of_day',
        'start_time',
        'end_time',
        'min_temp',
        'max_temp',
        'day_app_desc',
        'determinant',
        'charge_unit',
        'pending'
    ];

    protected $table = 'rateacuity_schedule_demandtime';
}