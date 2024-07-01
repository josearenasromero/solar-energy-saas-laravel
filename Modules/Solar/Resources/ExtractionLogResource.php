<?php

namespace Modules\Solar\Resources;

use Modules\Common\Resources\GenericResource;

class ExtractionLogResource extends GenericResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'entity_name' => $this->entity_name,
            'attempt' => $this->attempt,
            'status' => $this->status,
            'message' => $this->message,
            'start_extracted_date' => $this->start_extracted_date,
            'end_extracted_date' => $this->end_extracted_date,
            'attempt_date' => $this->attempt_date,
            'table_name' => $this->table_name,
            'key_name' => $this->key_name,
            'key_value' => $this->key_value,
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}