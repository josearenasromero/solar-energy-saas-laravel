<?php

namespace Modules\Solar\Http\Requests;
use App\Http\Request\ApiRequest;

class UserDeleteRequest extends ApiRequest
{
    public function rules()
    {
        return[
            'id' => ['required_without:email', 'uuid'],
            'email' => ['email', 'string', 'max:255']
        ];
    }

    public function messages()
    {
        return[
            'id.required_without' => 'ID or Email is required',
            'id.uuid' => 'ID is not valid',
            'email.string' => 'Email must be a string',
            'email.max' => 'Email is too long'
        ];
    }
}