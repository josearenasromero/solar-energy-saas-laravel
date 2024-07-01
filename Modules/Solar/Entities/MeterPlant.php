<?php

namespace Modules\Solar\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\UtilityAPI\Entities\Meter;
use Modules\RateAcuity\Entities\Schedule;
use Modules\QOS\Entities\Plant;

class MeterPlant extends Model
{
    protected $fillable =[
        'meter_id',
        'plant_id',
        'schedule_id',
        'is_generator',
    ];

    protected $table = 'meter_plant';

    public function meter()
    {
        return $this->hasOne(Meter::class, 'id', 'meter_id');
    }

    public function plant()
    {
        return $this->hasOne(Plant::class, 'id', 'plant_id');
    }

    public function schedule()
    {
        return $this->hasOne(Schedule::class, 'id', 'schedule_id');
    }
}