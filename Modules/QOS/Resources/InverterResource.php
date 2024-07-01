<?php

namespace Modules\QOS\Resources;

use Modules\Common\Resources\GenericResource;
use Modules\UtilityAPI\Resources\MeterResource;

class InverterResource extends GenericResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'plant_name' => $this->plant->name ?? null,
            'company_name' => $this->plant->company->name ?? null,
            'name' => $this->name,
            'group' => $this->group,
            'serial' => $this->serial,
            'manufacturer' => $this->manufacturer,
            'model' => $this->model,
            'peak_power' => $this->peak_power,
            'active' => $this->active,
            'company' => isset($this->plant->company) ? new CompanyResource($this->plant->company, false) : null, 
            'plant' => isset($this->plant) ? new PlantResource($this->plant, false) : null,
            'meter'=> isset($this->meter) ? new MeterResource($this->meter) : null,
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}