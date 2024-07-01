<?php

namespace Modules\UtilityAPI\Resources;

use Modules\Common\Resources\GenericCollection;

class AuthorizationCollection extends GenericCollection
{
    public function toArray($request)
    {
        return [
            'data' => [
                'items' => $this->collection,
                'total' => $this->total(),
            ],
        ];
    }

    public function withResponse($request, $response)
    {
        return parent::withResponse($request, $response);
    }
}
