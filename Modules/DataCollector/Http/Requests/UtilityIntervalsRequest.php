<?php

namespace Modules\DataCollector\Http\Requests;

use App\Http\Request\ApiRequest;

class UtilityIntervalsRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'custom_interval' => ['nullable', 'integer','min:1'],
            'custom_end' => ['nullable', 'string', 'max:255'],
            'custom_meter' => ['nullable', 'integer','min:1'],
            'plant_id' => ['nullable', 'integer','min:1'],
        ];
    }

    public function messages()
    {
        return [
        ];
    }
}
