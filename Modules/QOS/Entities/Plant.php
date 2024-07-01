<?php

namespace Modules\QOS\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\AlsoEnergy\App\Models\AESite;
use Modules\RateAcuity\Entities\ScheduleRate;
use Modules\RateAcuity\Entities\Utility;
use Modules\UtilityAPI\Entities\Authorization;
use Modules\UtilityAPI\Entities\Meter;
use Modules\Solar\Entities\MeterPlant;

class Plant extends Model
{

    protected $fillable = [
        'id',
        'name',
        'type',
        'peak_power',
        'commissioning_date',
        'computation_start_date',
        'timeZone',
        'latitude',
        'longitud',
        'company_id',
        'qos_plant_id',
        'authorization_id',
        'utility_id',
        'ae_site_id',
    ];

    protected $table = 'qos_plant';

    public function company()
    {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }

    public function utility()
    {
        return $this->hasOne(Utility::class, 'id', 'utility_id');
    }

    public function authorization()
    {
        return $this->hasOne(Authorization::class, 'id', 'authorization_id');
    }

    // public function scheduleRate()
    // {
    //     return $this->belongsToMany(ScheduleRate::class, 'schedulerate_plant', 'plant_id',  'schedulerate_id');
    // }

    public function meter()
    {
        return $this->belongsToMany(Meter::class, 'meter_plant', 'plant_id','meter_id');
    }

    public function meter_plant()
    {
        return $this->hasMany(MeterPlant::class, 'plant_id');
    }
    public function aeSite()
    {
        return $this->hasOne(AESite::class, 'id', 'ae_site_id');
    }
}
