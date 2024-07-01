<?php

namespace Modules\AlsoEnergy\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\QOS\Entities\Plant;

class AESite extends Model
{
    protected $fillable = [
        'id',
        'name',
        'latitude',
        'longitude',
        'timezone',
        'address1',
        'zip_code',
        'city',
        'state',
        'country',
        'ae_site_id',
        'plant_id',
        'turn_on_date'
    ];
    protected $table = 'ae_site';

    public function plant()
    {
        return $this->belongsTo(Plant::class, 'id');
    }
    public function aeHardware()
    {
        return $this->hasMany(AEHardware::class,'ae_site_id');
    }
    
    use HasFactory;
}
