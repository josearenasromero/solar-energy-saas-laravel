<?php

namespace Modules\RateAcuity\Http\Requests\ScheduleRate;

use App\Http\Request\ApiRequest;

class IndexRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'page' => ['integer','min:1'],
            'limit' => ['integer','min:1'],
            'schedule_id' =>  ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages()
    {
        return [
            'page.integer' => 'The page must be an integer',
            'page.min' => 'The page must be at least 1',
            'limit.integer' => 'The limit must be an integer',
            'limit.min' => 'The limit must be at least 1',
            'schedule_id.integer' => 'Schedule ID must be a integer',
        ];
    }
}
