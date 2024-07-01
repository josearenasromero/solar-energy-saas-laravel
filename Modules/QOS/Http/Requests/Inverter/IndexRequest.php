<?php

namespace Modules\QOS\Http\Requests\Inverter;
use App\Http\Request\ApiRequest;

class IndexRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'page' => ['integer', 'min:1'],
            'limit' => ['integer', 'min:1'],
            'plant_id' => ['nullable', 'integer', 'min:1'],
            'group' => ['nullable', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'active' => ['boolean'],
        ];
    }

    public function messages()
    {
        return [
            'page.integer' => 'The page must be an integer',
            'page.min' => 'The page must be at least 1',
            'limit.integer' => 'The limit must be an integer',
            'limit.min' => 'The limit must be at least 1',
            'company_id.integer' => 'Company ID must be an integer',
            'group.integer' => 'Group must be a string',
            'manufacturer.integer' => 'Manufacturer must be a string',
            'model.integer' => 'Model must be a string',
            'active.boolean' => 'Parameter only accepts true or false values',
        ];
    }
}