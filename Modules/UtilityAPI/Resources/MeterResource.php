<?php

namespace Modules\UtilityAPI\Resources;

use Modules\Common\Resources\GenericResource;
use Modules\QOS\Resources\InverterResource;
use Modules\RateAcuity\Resources\ScheduleResource;

class MeterResource extends GenericResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'utilityapi_meter_id' => $this->utilityapi_meter_id,
            'service_class' => $this->service_class,
            'service_tariff' => $this->service_tariff,
            'service_address' => $this->service_address,
            'service_identifier' => $this->service_identifier,
            'meter_numbers' => $this->meter_numbers,
            'billing_account' => $this->billing_account,
            'billing_address' => $this->billing_address,
            'billing_contact' => $this->billing_contact,
            'authorization_id' => $this->authorization_id,
            'schedule'=> isset($this->schedule) ? new ScheduleResource($this->schedule) : null,
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}