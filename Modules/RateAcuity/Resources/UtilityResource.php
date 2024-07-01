<?php

namespace Modules\RateAcuity\Resources;

use Modules\Common\Resources\GenericResource;
use Modules\RateAcuity\Resources\ScheduleResource;

class UtilityResource extends GenericResource
{
    private $detailed;
    public function __construct($resource, $detailed = true)
    {
        parent::__construct($resource);
        $this->detailed = $detailed;
    }

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'utility_id' => $this->utility_id,
            'name' => $this->name,
            'state' => $this->state,
            'schedules' => (isset($this->schedules) && $this->detailed) ? ScheduleResource::collection($this->schedules) : null,
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}
