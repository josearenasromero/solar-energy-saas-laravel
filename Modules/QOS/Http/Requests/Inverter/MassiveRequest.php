<?php

namespace Modules\QOS\Http\Requests\Inverter;
use App\Http\Request\ApiRequest;

class MassiveRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'page' => ['integer', 'min:1'],
            'limit' => ['integer', 'min:1'],
            'ids' => ['required', 'array'],
            'active' => ['integer', 'min:0', 'max:1'],
        ];
    }

    public function messages()
    {
        return [
            'page.integer' => 'The page must be an integer',
            'page.min' => 'The page must be at least 1',
            'limit.integer' => 'The limit must be an integer',
            'limit.min' => 'The limit must be at least 1',
            'ids.required' => 'IDs is required',
            'ids.array' => 'IDs must be an array',
            'active.boolean' => 'Parameter only accepts 1 or 0 values',
        ];
    }
}