<?php

namespace Modules\AlsoEnergy\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AEMeasurement extends Model
{
    protected $fillable = [
        'id',
        'ae_hardware_id',
        'bin_size',
        'timezone',
        'collected_at',
        'value'
        
    ];
    protected $table = 'ae_measurement';
    public function aeHardware()
    {        
        return $this->hasOne(AEHardware::class, 'id', 'ae_hardware_id');

    }
    use HasFactory;
}
