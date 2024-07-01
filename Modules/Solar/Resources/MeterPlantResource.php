<?php

namespace Modules\Solar\Resources;

use Modules\Common\Resources\GenericResource;
use Modules\UtilityAPI\Resources\MeterResource;
use Modules\RateAcuity\Resources\ScheduleResource;

class MeterPlantResource extends GenericResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'meter_id' => $this->meter_id,
            'plant_id' => $this->plant_id,
            'schedule_id' => $this->schedule_id,
            'meter' => isset($this->meter) ? new MeterResource($this->meter) : null,
            'schedule' => isset($this->schedule) ? new ScheduleResource($this->schedule) : null,
            'is_generator' => $this->is_generator,
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}