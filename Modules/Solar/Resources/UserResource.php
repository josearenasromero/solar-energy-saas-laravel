<?php

namespace Modules\Solar\Resources;

use Modules\Common\Resources\GenericResource;

class UserResource extends GenericResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}