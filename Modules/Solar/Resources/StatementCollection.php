<?php

namespace Modules\Solar\Resources;

use Modules\Common\Resources\GenericCollection;

class StatementCollection extends GenericCollection
{
    public function __construct($resource, $detailed = true)
    {
        parent::__construct($resource);
    }
    public function toArray($request)
    {
        $transformed = $this->collection->map(function ($item) {
            return new StatementResource($item);
        });

        return [
            'data' => [
                'items' => $transformed,
            ],
        ];
    }

    public function withResponse($request, $response)
    {
        return parent::withResponse($request, $response);
    }
}
