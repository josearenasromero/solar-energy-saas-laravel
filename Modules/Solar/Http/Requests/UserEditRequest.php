<?php

namespace Modules\Solar\Http\Requests;
use App\Http\Request\ApiRequest;
use App\Enums\UserRole;
use Illuminate\Validation\Rules\Enum;

class UserEditRequest extends ApiRequest
{
    public function rules()
    {
        return[
            'id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'string', 'max:255'],
            'role' => [new Enum(UserRole::class)]
        ];
    }

    public function messages()
    {
        return[
            'id.required' => 'ID is required',
            'id.uuid' => 'ID is not valid',
            'name.required' => 'Name is required',
            'name.string' => 'Name must be a string',
            'name.max' => 'Name is too long',
            'email.required' => 'Email is required',
            'email.string' => 'Email must be a string',
            'email.max' => 'Email is too long',
            'role.enum' => 'Role is not valid'
        ];
    }
}