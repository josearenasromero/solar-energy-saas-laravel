<?php

namespace Modules\UtilityAPI\Resources;

use Modules\Common\Resources\GenericResource;

class MeasurementResource extends GenericResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'meter_id' => $this->meter_id,
            'utilityapi_interval_uid' => $this->utilityapi_interval_uid,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'kwh_value' => $this->kwh_value,
            'datapoints' => json_decode($this->datapoints, true),
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}