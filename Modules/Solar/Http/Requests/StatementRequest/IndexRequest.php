<?php

namespace Modules\Solar\Http\Requests\StatementRequest;

use App\Http\Request\ApiRequest;

class IndexRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'page' => ['integer','min:1'],
            'limit' => ['integer','min:1'],
            'company_id' => [ 'integer', 'min:1'],
            'plant_id' => [ 'integer', 'min:1'],
            'isAE' => [ 'boolean','nullable'],
        ];
    }

    public function messages()
    {
        return [
            'page.integer' => 'The page must be an integer',
            'page.min' => 'The page must be at least 1',
            'limit.integer' => 'The limit must be an integer',
            'limit.min' => 'The limit must be at least 1',
            'company_id.integer' => 'The Company ID must be an integer',
            'plant_id.integer' => 'The Plant ID must be an integer',
        ];
    }
}
