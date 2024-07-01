<?php

namespace Modules\Common\Resources;

use Modules\Common\Resources\GenericResource;

class ApiLimitResource extends GenericResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'api' => $this->api,
            'minute_limit' => $this->minute_limit,
            'daily_limit' => $this->daily_limit,
            'monthly_limit' => $this->monthly_limit,
            'yearly_limit' => $this->yearly_limit,
            'minute_count' => $this->minute_count,
            'daily_count' => $this->daily_count,
            'monthly_count' => $this->monthly_count,
            'yearly_count' => $this->yearly_count,
            'minute_reset' => $this->minute_reset,
            'daily_reset' => $this->daily_reset,
            'monthly_reset' => $this->monthly_reset,
            'yearly_reset' => $this->yearly_reset,
            'minute_last' => $this->minute_last,
            'daily_last' => $this->daily_last,
            'monthly_last' => $this->monthly_last,
            'yearly_last' => $this->yearly_last,
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}