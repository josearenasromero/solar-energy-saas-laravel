<?php

namespace Modules\RateAcuity\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\UtilityAPI\Entities\Meter;

class Schedule extends Model
{
    protected $fillable =[
        'pending',
        'schedule_id',
        'utility_id',
        'schedule_name',
        'schedule_description',
        'use_type',
        'min_demand',
        'max_demand',
        'min_usage',
        'max_usage',
        'effective_date',
        'option_type',
        'option_description',
        'utility_name',
        'state'
    ];

    protected $table = 'rateacuity_schedule';

    public function rates()
    {
        return $this->hasMany(ScheduleRate::class, 'schedule_id', 'id');
    }

    public function energy_time()
    {
        return $this->hasMany(ScheduleEnergyTime::class, 'schedule_id', 'id');
    }

    public function incremental_energy()
    {
        return $this->hasMany(ScheduleIncrementalEnergy::class, 'schedule_id', 'id');
    }

    public function meter()
    {
        return $this->belongsTo(Meter::class);
    }
}
