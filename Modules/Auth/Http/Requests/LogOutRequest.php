<?php

namespace Modules\Auth\Http\Requests;
use App\Http\Request\ApiRequest;

class LogOutRequest extends ApiRequest
{
    public function rules()
    {
        return[
        ];
    }

    public function messages()
    {
        return[
        ];
    }
}