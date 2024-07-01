<?php

namespace Modules\QOS\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\RateAcuity\Entities\ScheduleRate;
use Modules\Solar\Entities\CompanySchedule;
use Modules\UtilityAPI\Entities\Authorization;
use Modules\RateAcuity\Entities\Utility;

class Company extends Model
{

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'timezone',
        'address1',
        'address2',
        'zip_code',
        'city',
        'country',
        'owner_first_name',
        'owner_last_name',
        'owner_email',
        'fitter_first_name',
        'fitter_last_name',
        'fitter_email',
        'manager_first_name',
        'manager_last_name',
        'manager_email',
        'qos_site_id',
        'active',
    ];

    protected $table = 'qos_company';

    public function plants()
    {
        return $this->hasMany(Plant::class);
    }

    public function schedules()
    {
        return $this->hasMany(CompanySchedule::class, 'company_id', 'id');
    }


}
