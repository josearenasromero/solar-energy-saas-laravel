<?php

namespace Modules\QOS\Http\Requests\Company;
use App\Http\Request\ApiRequest;

class EditRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'id' => 'integer|min:1',
            'active' => 'nullable|boolean',
            'authorization_id' => 'nullable|integer|min:1',
            'utility_id' => 'nullable|integer|min:1'
        ];
    }

    public function messages()
    {
        return [
            'id' => 'The Company ID must be an integer',
            'active.boolean' => 'Parameter only accepts true or false values',
            'authorization_id.string' => 'Authorization ID must be an string',
            'schedulerate_id.integer' => 'Schedule Rate ID must be an integer'
        ];
    }
}