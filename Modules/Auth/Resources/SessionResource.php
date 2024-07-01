<?php

namespace Modules\Auth\Resources;

use Modules\Common\Resources\GenericResource;

class SessionResource extends GenericResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'role' => $this->user->role,
            'token' => $this->token->accessToken,
            'created_at' => $this->token->token->created_at ?? null,
            'expires_at' => $this->token->token->expires_at ?? null
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}