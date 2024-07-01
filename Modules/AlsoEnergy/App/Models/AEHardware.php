<?php


namespace Modules\AlsoEnergy\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AEHardware extends Model
{
    protected $fillable = [
        'id',
        'ae_hardware_id',
        'ae_hardware_str_id',
        'name',
        'device_type',
        'serial',
        'field_name',
        'rated_ac_power',
        'string_count',
        'ae_site_id'
    ];
    protected $table = 'ae_hardware';

    public function aeSites()
    {
        return $this->hasOne(AESite::class, 'id', 'ae_site_id');
    }

    public function aeMeasurements()
    {
        return $this->hasMany(AEMeasurement::class,'ae_hardware_id');
    }


    use HasFactory;
}
