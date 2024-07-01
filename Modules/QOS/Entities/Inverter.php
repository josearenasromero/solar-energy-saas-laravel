<?php 

namespace Modules\QOS\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\UtilityAPI\Entities\Meter;

class Inverter extends Model
{

    protected $fillable = [
        'name',
        'group',
        'serial',
        'manufacturer',
        'model',
        'peak_power',
        'plant_id',
        'active',
        'qos_inverter_id',
        'meter_id',
    ];

    protected $table = 'qos_inverter';

    public function plant()
    {
        return $this->hasOne(Plant::class, 'id', 'plant_id');
    }

    public function sensors()
    {
        return $this->hasMany(Sensor::class);
    }

    public function meter()
    {
        return $this->hasOne(Meter::class, 'id', 'meter_id');
    }

}