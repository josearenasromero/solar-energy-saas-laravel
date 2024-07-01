<?php

namespace Modules\Auth\Http\Requests;
use App\Http\Request\ApiRequest;

class LoginRequest extends ApiRequest
{
    public function rules()
    {
        return[
            'email' => ['required', 'email', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6']
        ];
    }

    public function messages()
    {
        return[
            'email.required' => 'Email is required',
            'email.string' => 'Email must be a string',
            'email.max' => 'Email is too long',
            'password.required' => 'Password is required',
            'password.string' => 'Password must be a string',
            'password.min' => 'Password is too short'
        ];
    }
}