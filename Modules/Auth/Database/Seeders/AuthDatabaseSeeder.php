<?php

namespace Modules\Auth\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class AuthDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@anala.com',
                'password' => Hash::make('secret123'),
                'role' => 'admin'
            ],
            [
                'name' => 'Mike Reall',
                'email' => 'mike@anala.com',
                'password' => Hash::make('secret123'),
                'role' => 'user'
            ],
        ];

        foreach ($users as $user){
            //User::create($user);
        }
        
    }
}
