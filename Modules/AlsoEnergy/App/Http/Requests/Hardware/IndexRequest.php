<?php

namespace Modules\AlsoEnergy\App\Http\Requests\Hardware;
use App\Http\Request\ApiRequest;

class IndexRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'page' => ['integer', 'min:1'],
            'limit' => ['integer', 'min:1'],
            'ae_site_id' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'page.integer' => 'The page must be an integer',
            'page.min' => 'The page must be at least 1',
            'limit.integer' => 'The limit must be an integer',
            'limit.min' => 'The limit must be at least 1',
            'ae_site_id.integer' => 'Site ID must be a string',
        ];
    }
}