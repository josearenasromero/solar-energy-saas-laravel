<?php

namespace Modules\RateAcuity\Resources;

use Modules\Common\Resources\GenericResource;

class ScheduleEnergyTimeResource extends GenericResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'pending' => $this->pending,
            'description' => $this->description,
            'rate_kwh' => $this->rate_kwh,
            'min_kv' => $this->min_kv,
            'max_kv' => $this->max_kv,
            'determinant' => $this->determinant,
            'season' => $this->season,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'max_temp' => $this->max_temp,
            'min_temp' => $this->min_temp,
            'charge_unit' => $this->charge_unit,
            'time_of_day' => $this->time_of_day,
            'day_app_desc' => $this->day_app_desc,
            'schedule'=> isset($this->schedule) ? new ScheduleResource($this->schedule) : null,
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}
