<?php

namespace Modules\RateAcuity\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\QOS\Entities\Plant;

class Utility extends Model
{
    protected $fillable = [
        'utility_id',
        'name',
        'state'
    ];

    protected $table = 'rateacuity_utility';

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'utility_id', 'id');
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }
}
