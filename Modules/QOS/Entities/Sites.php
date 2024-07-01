<?php

namespace Modules\QOS\Entities;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Sites extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'company_id'
    ];

    protected $table = 'qos_sites';
}