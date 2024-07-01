<?php

namespace Modules\Common\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class GenericCollection extends ResourceCollection
{
    public function toArray($request)
    {   
        return parent::toArray($request);
    }

    public function with($request)
    {
        return [
            'status' => true,
            'meta'=> null,
            'error' => null
        ];
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}