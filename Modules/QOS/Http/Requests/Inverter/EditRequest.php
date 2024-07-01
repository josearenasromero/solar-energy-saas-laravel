<?php

namespace Modules\QOS\Http\Requests\Inverter;
use App\Http\Request\ApiRequest;

class EditRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'active' => ['integer', 'min:0', 'max:1'],
            'meter_id'=> ['integer','min:1'],
        ];
    }

    public function messages()
    {
        return [
            'active.boolean' => 'Parameter only accepts 1 or 0 values',
            'meter_id.integer' => 'Meter ID must be a integer',
        ];
    }
}