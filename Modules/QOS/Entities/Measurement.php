<?php 

namespace Modules\QOS\Entities;
use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{

    protected $fillable = [
        'id',
        'sensor_id',
        'collected_at',
        'timezone',
        'value'
    ];

    protected $table = 'qos_measurement';

    public function sensor()
    {
        return $this->hasOne(Sensor::class, 'id', 'sensor_id');
    }
}