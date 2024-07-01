<?php

namespace Modules\QOS\Http\Requests\Plant;
use App\Http\Request\ApiRequest;

class EditRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'id' => 'integer|min:1',
            'authorization_id' => 'nullable|integer|min:1',
            'utility_id' => 'nullable|integer|min:1',
            'ae_site_id' => 'nullable|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'id' => 'The Plant ID must be an integer',
            'authorization_id.integer' => 'Authorization ID must be an integer',
            'utility_id.integer' => 'Utility ID must be an integer',
        ];
    }
}
