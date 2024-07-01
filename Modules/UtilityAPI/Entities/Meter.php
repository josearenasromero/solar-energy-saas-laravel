<?php

namespace Modules\UtilityAPI\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\QOS\Entities\Inverter;
use Modules\RateAcuity\Entities\Schedule;
use Modules\Solar\Entities\MeterPlant;

class Meter extends Model
{

    protected $fillable = [
        'id',
        'utilityapi_meter_id',
        'service_class',
        'service_tariff',
        'service_address',
        'service_identifier',
        'meter_numbers',
        'billing_account',
        'billing_address',
        'billing_contact',
        'authorization_id',
        'schedule_id'
    ];

    protected $table = 'utilityapi_meter';

    public function inverters()
    {
        return $this->hasMany(Inverter::class);
    }

    public function schedule()
    {
        return $this->hasOne(Schedule::class, 'schedule_id');
    }
    public function meter_plant()
    {
        return $this->hasMany(MeterPlant::class, 'meter_id');
    }
}
