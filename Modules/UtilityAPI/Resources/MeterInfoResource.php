<?php

namespace Modules\UtilityAPI\Resources;

use Modules\Common\Resources\GenericResource;
class MeterInfoResource extends GenericResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'bill_start_date' => $this->bill_start_date,
            'bill_end_date' => $this->bill_end_date,
            'bill_season' => $this->bill_season,
            'net_on_peak' => $this->net_on_peak,
            'net_part_peak' => $this->net_part_peak,
            'net_off_peak' => $this->net_off_peak,
            'max_nc' => $this->max_nc,
            'max_on' => $this->max_on,
            'charge_other' => $this->charge_other,
            'charge_nbc' => $this->charge_nbc,
            'charge_energy' => $this->charge_energy,
            'charge_demand' => $this->charge_demand,
            'charge_total' => $this->charge_total,
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}
