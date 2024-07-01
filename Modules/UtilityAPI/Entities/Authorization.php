<?php

namespace Modules\UtilityAPI\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\QOS\Entities\Plant;

class Authorization extends Model
{

    protected $fillable = [
        'id',
        'customer_email',
        'customer_signature_full_name',
        'nickname',
        'utility_id',
        'user_email',
        'user_uid',
        'utility',
    ];

    protected $table = 'utilityapi_authorization';

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }
}
