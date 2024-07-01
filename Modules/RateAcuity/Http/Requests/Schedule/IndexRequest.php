<?php

namespace Modules\RateAcuity\Http\Requests\Schedule;

use App\Http\Request\ApiRequest;

class IndexRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'page' => ['integer','min:1'],
            'limit' => ['integer','min:1'],
            'utility_id' =>  ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'page.integer' => 'The page must be an integer',
            'page.min' => 'The page must be at least 1',
            'limit.integer' => 'The limit must be an integer',
            'limit.min' => 'The limit must be at least 1',
            'utility_id.integer' => 'Utility ID must be a integer',
        ];
    }
}
