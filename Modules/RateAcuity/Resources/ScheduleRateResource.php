<?php

namespace Modules\RateAcuity\Resources;

use Modules\Common\Resources\GenericResource;

class ScheduleRateResource extends GenericResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'schedule_id' => $this->schedule_id,
            'real_schedule_id' => $this->real_schedule_id,
            'description' => $this->description,
            'rate_kwh' => $this->rate_kwh,
            'min_kv' => $this->min_kv,
            'max_kv' => $this->max_kv,
            'determinant' => $this->determinant,
            'charge_unit' => $this->charge_unit,
            'pending' => $this->pending
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}