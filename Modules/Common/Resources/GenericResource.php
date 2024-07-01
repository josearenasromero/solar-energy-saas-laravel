<?php

namespace Modules\Common\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GenericResource extends JsonResource
{
    protected $message;

    public function message($value)
    {
        $this->message = $value;
        return $this;
    }

    public function toArray($request)
    {   
        return parent::toArray($request);
    }

    public function with($request)
    {
        return [
            'status' => true,
            'message' => $this->message,
            'meta'=> null,
            'error' => null
        ];
    }
}