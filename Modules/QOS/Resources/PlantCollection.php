<?php

namespace Modules\QOS\Resources;

use Modules\Common\Resources\GenericCollection;

class PlantCollection extends GenericCollection
{   private $detailed;
    public function __construct($resource, $detailed = true)
    {
        parent::__construct($resource);
        $this->detailed = $detailed;
    }
    public function toArray($request)
    {
        $transformed = $this->collection->map(function ($item) {
            return new PlantResource($item, $this->detailed);
        });

        return [
            'data' => [
                'items' => $transformed,
                'total' => $this->total(), 
            ],
        ];
    }

    public function withResponse($request, $response)
    {
        return parent::withResponse($request, $response);
    }
}
