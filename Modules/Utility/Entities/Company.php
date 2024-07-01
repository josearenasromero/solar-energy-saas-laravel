<?php 

namespace Modules\Utility\Entities;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{

    use HasUuids;

    protected $fillable = [
        'name',
        'qos_id',
        'utilityapi_id'
    ];

    protected $table = 'company';

}