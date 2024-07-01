<?php

namespace Modules\QOS\Resources;

use Modules\Common\Resources\GenericResource;

class MeasurementResource extends GenericResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sensor_id' => $this->sensor_id,
            'collected_at' => $this->collected_at,
            'timezone' => $this->timezone,
            'value' => $this->value,
            'sensor' => (new SensorResource($this->sensor))->only('id', 'inverter_id')
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}