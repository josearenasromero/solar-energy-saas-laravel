<?php

namespace Modules\AlsoEnergy\Resources;

use Modules\Common\Resources\GenericResource;

class AEMeasurementResource extends GenericResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'ae_hardware_id' => $this->ae_hardware_id,
            'bin_size' => $this->bin_size,
            'timezone' => $this->timezone,
            'collected_at' => $this->collected_at,
            'value' => $this->value
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}