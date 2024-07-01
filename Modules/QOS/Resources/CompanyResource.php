<?php

namespace Modules\QOS\Resources;

use Modules\Common\Resources\GenericResource;
use Modules\RateAcuity\Resources\ScheduleRateResource;
use Modules\RateAcuity\Resources\ScheduleResource;
use Modules\RateAcuity\Resources\UtilityResource;
use Modules\UtilityAPI\Resources\AuthorizationResource;

class CompanyResource extends GenericResource
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
            'name' => $this->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'timezone' => $this->timezone,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'zip_code' => $this->zip_code,
            'city' => $this->city,
            'country' => $this->country,
            'owner_first_name' => $this->owner_first_name,
            'owner_last_name' => $this->owner_last_name,
            'owner_email' => $this->owner_email,
            'fitter_first_name' => $this->fitter_first_name,
            'fitter_last_name' => $this->fitter_last_name,
            'fitter_email' => $this->fitter_email,
            'manager_first_name' => $this->manager_first_name,
            'manager_last_name' => $this->manager_last_name,
            'manager_email' => $this->manager_email,
            'qos_site_id' => $this->qos_site_id,
            'active' => $this->active,
            'plants' => (isset($this->plants) && $this->detailed) ? PlantResource::collection($this->plants) : null,
            'schedule_rates' => (isset($this->scheduleRate) && $this->detailed) ? ScheduleRateResource::collection($this->scheduleRate) : null,
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}
