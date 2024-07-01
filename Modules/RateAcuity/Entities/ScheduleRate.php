<?php

namespace Modules\RateAcuity\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\QOS\Entities\Company;

class ScheduleRate extends Model
{
    protected $fillable =[
        'schedule_id',
        'real_schedule_id',
        'description',
        'rate_kwh',
        'min_kv',
        'max_kv',
        'determinant',
        'charge_unit',
        'pending'
    ];

    protected $table = 'rateacuity_schedulerate';

    /*public function company()
    {
        return $this->belongsToMany(Company::class, 'schedulerate_company', 'schedulerate_id', 'company_id');
    }*/
}