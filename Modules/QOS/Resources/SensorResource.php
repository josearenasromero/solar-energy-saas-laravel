<?php

namespace Modules\QOS\Resources;

use Modules\Common\Resources\GenericResource;

class SensorResource extends GenericResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'inverter_id' => $this->inverter_id,
            'name' => $this->name,
            'description' => $this->description,
            'formula' => $this->formula,
            'referent' => $this->referent,
            'sampling' => $this->sampling,
            'day_aggregation' => $this->day_aggregation,
            'month_aggregation' => $this->month_aggregation,
            'unit' => $this->unit,
            'sensor_type' => $this->sensor_type,
            'inverter' => (new InverterResource($this->inverter))->only('id', 'company_id', 'name', 'group', 'serial', 'manufacturer', 'model', 'peak_power')
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}