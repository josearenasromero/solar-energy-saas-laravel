<?php

namespace Modules\UtilityAPI\Http\Requests\Meter;
use App\Http\Request\ApiRequest;

class IndexRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'page' => ['integer', 'min:1'],
            'limit' => ['integer', 'min:1'],
            'utilityapi_meter_id'=> ['integer','min:1'],
            'meter_id' => ['integer', 'min:1'],
            'schedule_id' => ['integer', 'min:1'],
            'plant_id' => ['integer', 'min:1'],
            'start_date' => ['string'],
            'end_date' => ['string'],
            'is_ae' => ['boolean','nullable'],
        ];
    }

    public function messages()
    {
        return [
            'page.integer' => 'The page must be an integer',
            'page.min' => 'The page must be at least 1',
            'limit.integer' => 'The limit must be an integer',
            'limit.min' => 'The limit must be at least 1',
            'utilityapi_meter_id.integer' => 'Utilityapi Meter ID must be an integer',
        ];
    }
}