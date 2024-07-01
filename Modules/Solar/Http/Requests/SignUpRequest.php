<?php

namespace Modules\Solar\Http\Requests;
use App\Http\Request\ApiRequest;
use App\Enums\UserRole;
use Illuminate\Validation\Rules\Enum;

class SignUpRequest extends ApiRequest
{
    public function rules()
    {
        return[
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            'role' => [new Enum(UserRole::class)]
        ];
    }

    public function messages()
    {
        return[
            'name.required' => 'Name is required',
            'name.string' => 'Name must be a string',
            'name.max' => 'Name is too long',
            'email.required' => 'Email is required',
            'email.string' => 'Email must be a string',
            'email.max' => 'Email is too long',
            'password.required' => 'Password is required',
            'password.string' => 'Password must be a string',
            'password.min' => 'Password is too short',
            'role.enum' => 'Role is not valid'
        ];
    }
}