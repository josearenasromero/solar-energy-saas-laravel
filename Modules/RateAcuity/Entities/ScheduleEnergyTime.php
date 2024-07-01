<?php

namespace Modules\RateAcuity\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\QOS\Entities\Company;
use Modules\RateAcuity\Entities\Schedule;

class ScheduleEnergyTime extends Model
{
    protected $fillable =[
        'pending',
        'schedule_id',
        'description',
        'rate_kwh',
        'min_kv',
        'max_kv',
        'determinant',
        'season',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'max_temp',
        'min_temp',
        'charge_unit',
        'day_app_desc',
        'time_of_day'
    ];

    protected $table = 'rateacuity_schedule_energytime';

    public function schedule()
    {
        return $this->hasOne(Schedule::class, 'id', 'schedule_id');
    }

}
