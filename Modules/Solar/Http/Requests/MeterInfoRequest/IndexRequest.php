<?php

namespace Modules\Solar\Http\Requests\MeterInfoRequest;

use App\Http\Request\ApiRequest;

class IndexRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'page' => ['integer','min:1'],
            'limit' => ['integer','min:1'],
            'meter_id' => [ 'integer', 'min:1'],
            'schedule_id' => [ 'integer', 'min:1'],
        ];
    }

    public function messages()
    {
        return [
            'page.integer' => 'The page must be an integer',
            'page.min' => 'The page must be at least 1',
            'limit.integer' => 'The limit must be an integer',
            'limit.min' => 'The limit must be at least 1',
            'meter_id.integer' => 'The Company ID must be an integer',
            'schedule_id.integer' => 'The Schedule ID must be an integer',
        ];
    }
}
