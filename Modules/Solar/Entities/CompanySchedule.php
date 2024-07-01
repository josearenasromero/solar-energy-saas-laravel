<?php

namespace Modules\Solar\Entities;
use Illuminate\Database\Eloquent\Model;

class CompanySchedule extends Model
{
    protected $fillable =[
        'company_id',
        'schedule_id',
    ];

    protected $table = 'company_schedule';
}