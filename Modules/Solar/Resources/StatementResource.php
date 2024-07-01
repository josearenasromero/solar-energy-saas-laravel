<?php

namespace Modules\Solar\Resources;

use Modules\Common\Resources\GenericResource;
use Modules\QOS\Resources\PlantResource;

class StatementResource extends GenericResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'meter' => $this->meter,
            'total_usage' => $this->total_usage,
            'net_usage' => $this->net_usage,
            'bill_w_o_solar' => $this->bill_w_o_solar,
            'bill_w_solar' => $this->bill_w_solar,
            'usage_detail' => $this->usage_detail,
            'savings' => $this->savings,
            'plant' => (isset($this->plant)) ? new PlantResource($this->plant, false) : null,
            'type' => $this->type,
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}
