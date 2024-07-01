<?php

namespace Modules\Auth\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\LogOutRequest;
use Modules\Auth\Resources\SessionResource;
use Modules\Solar\Resources\UserResource;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if(!$user)
        {
            throw new NotFoundException('User not found');
        }

        if(!Hash::check($request->password, $user->password))
        {
            throw new BadRequestException('Password mismatch');
        }

        $token = $user->createToken('api');

        return new SessionResource((object) ['user' => $user, 'token' => $token]);
    }

    public function logout(LogOutRequest $request)
    {
        $user = $request->user();
        $token = $user->token();

        if(!$token)
        {
            throw new BadRequestException('There was a problem retrieving the token');
        }

        $token->revoke();

        return new UserResource($user);
    }
}
