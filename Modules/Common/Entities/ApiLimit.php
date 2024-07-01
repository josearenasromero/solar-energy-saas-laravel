<?php

namespace Modules\Common\Entities;

use Illuminate\Database\Eloquent\Model;
use Exception;

class ApiLimit extends Model
{

    protected $fillable = [
        'id',
        'api',
        'minute_limit',
        'daily_limit',
        'monthly_limit',
        'yearly_limit',
        'minute_count',
        'daily_count',
        'monthly_count',
        'yearly_count',
        'minute_reset',
        'daily_reset',
        'monthly_reset',
        'yearly_reset',
        'minute_last',
        'daily_last',
        'monthly_last',
        'yearly_last'
    ];

    protected $table = 'api_limit';
    
}
