<?php

namespace Modules\QOS\Http\Requests\Company;
use App\Http\Request\ApiRequest;

class IndexRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'page' => ['integer', 'min:1'],
            'limit' => ['integer', 'min:1'],
            'only_active' => ['boolean'],
        ];
    }

    public function messages()
    {
        return [
            'page.integer' => 'The page must be an integer',
            'page.min' => 'The page must be at least 1',
            'limit.integer' => 'The limit must be an integer',
            'limit.min' => 'The limit must be at least 1',
            'only_active.boolean' => 'Parameter only accepts true or false values',
        ];
    }
}