<?php

namespace Modules\AlsoEnergy\Resources;

use Modules\Common\Resources\GenericResource;

class AEHardwareResource extends GenericResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'ae_hardware_id' => $this->ae_hardware_id,
            'ae_hardware_str_id' => $this->ae_hardware_str_id,
            'name' => $this->name,
            'device_type' => $this->device_type,
            'serial' => $this->serial,
            'field_name' => $this->field_name,
            'rated_ac_power' => $this->rated_ac_power,
            'string_count' => $this->string_count,
            'ae_site_id' => $this->ae_site_id
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}