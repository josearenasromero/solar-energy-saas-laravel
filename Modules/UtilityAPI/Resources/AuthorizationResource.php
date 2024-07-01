<?php

namespace Modules\UtilityAPI\Resources;

use Modules\Common\Resources\GenericResource;

class AuthorizationResource extends GenericResource
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
            'customer_email' => $this->customer_email,
            'customer_signature_full_name' => $this->customer_signature_full_name,
            'nickname' => $this->nickname,
            'utility_id' => $this->utility_id,
            'user_email' => $this->user_email,
            'user_uid' => $this->user_uid,
            'utility' => $this->utility,
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}
