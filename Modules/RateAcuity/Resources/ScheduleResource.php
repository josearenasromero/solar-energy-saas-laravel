<?php

namespace Modules\RateAcuity\Resources;

use Modules\Common\Resources\GenericResource;

class ScheduleResource extends GenericResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'pending' => $this->id,
            'schedule_id' => $this->schedule_id,
            'utility_id' => $this->utility_id,
            'schedule_name' => $this->schedule_name,
            'schedule_description' => $this->schedule_description,
            'use_type' => $this->use_type,
            'min_demand' => $this->min_demand,
            'max_demand' => $this->max_demand,
            'min_usage' => $this->min_usage,
            'max_usage' => $this->max_usage,
            'effective_date' => $this->effective_date,
            'option_type' => $this->option_type,
            'option_description' => $this->option_description,
            'utility_name' => $this->utility_name,
            'state' => $this->state,
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}