<?php 

namespace Modules\QOS\Entities;
use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{

    protected $fillable = [
        'id',
        'name',
        'description',
        'formula',
        'referent',
        'sampling',
        'day_aggregation',
        'month_aggregation',
        'unit',
        'sensor_type',
        'inverter_id',
        'qos_sensor_id'
    ];

    protected $table = 'qos_sensor';

    public function inverter()
    {
        return $this->hasOne(Inverter::class, 'id', 'inverter_id');
    }

    public function measurements()
    {
        return $this->hasMany(Measurement::class);
    }
}