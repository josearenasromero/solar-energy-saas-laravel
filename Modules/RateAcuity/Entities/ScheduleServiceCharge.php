<?php

namespace Modules\RateAcuity\Entities;
use Illuminate\Database\Eloquent\Model;

class ScheduleServiceCharge extends Model
{
    protected $fillable =[
        'schedule_id',
        'description',
        'rate',
        'charge_unit',
        'pending'
    ];

    protected $table = 'rateacuity_schedule_servicecharge';
}