<?php

namespace Modules\Solar\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use Modules\Solar\Resources\UserCollection;
use Modules\Solar\Resources\UserResource;
use Modules\Solar\Http\Requests\SignUpRequest;
use Modules\Solar\Http\Requests\UserEditRequest;
use Modules\Solar\Http\Requests\UserDeleteRequest;

class UserController extends Controller
{
    public function index()
    {
        $users = User::get();

        return new UserCollection($users, false);
    }

    public function get($id)
    {
        $user = User::find($id);

        return new UserResource($user);
    }

    public function create(SignUpRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user)
        {
            throw new BadRequestException('');
        }

        $new_user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        if(!$new_user)
        {
            throw new BadRequestException('There was a problem creating user');
        }

        return new UserResource($new_user);
    }

    public function update(UserEditRequest $request)
    {
        $user = User::where('id', $request->id)->first();

        if(!$user)
        {
            throw new NotFoundException('User not found');
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        $user->save();

        return new UserResource($user);
    }

    public function delete(UserDeleteRequest $request)
    {
        $id = $request->id;

        if ($id) {
            $user = User::where('id', $id)->first();
        } else {
            $user = User::where('email', $request->email)->first();
        }

        if(!$user)
        {
            throw new NotFoundException('User not found');
        }

        $user->delete();

        return new UserResource($user);
    }
}
