<?php

namespace Modules\UtilityAPI\Http\Requests\Meter;
use App\Http\Request\ApiRequest;

class EditRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'meter_id' => 'nullable|integer|min:1',
            'plant_id' => 'nullable|integer|min:1',
            'schedule_id' => 'nullable|integer|min:1',
            'is_generator' => 'nullable|integer',
        ];
    }

    public function messages()
    {
        return [
            'schedule_id.integer' => 'Schedule ID must be an integer'
        ];
    }
}