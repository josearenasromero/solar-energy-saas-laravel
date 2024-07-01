<?php

namespace Modules\UtilityAPI\Http\Requests\Utility;

use App\Http\Request\ApiRequest;

class IndexRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'page' => ['integer','min:1'],
            'limit' => ['integer','min:1'],
            'id' => ['nullable', 'integer', 'min:1'],
            'utilityapi_id' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'page.integer' => 'The page must be an integer',
            'page.min' => 'The page must be at least 1',
            'limit.integer' => 'The limit must be an integer',
            'limit.min' => 'The limit must be at least 1',
            'id.integer' => 'The Utility ID must be an integer',
            'utilityapi_id.integer' => 'Utility Name must be a string',
        ];
    }
}
