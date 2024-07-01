<?php

namespace Modules\AlsoEnergy\App\Http\Requests\Site;
use App\Http\Request\ApiRequest;

class EditRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'id' => 'integer|min:1',
            'plant_id' => 'nullable|integer|min:1'            
        ];
    }

    public function messages()
    {
        return [
            'id' => 'The Plant ID must be an integer',
            'plant_id.integer' => 'Plant ID must be an integer'
        ];
    }
}
