<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Support\Facades\Hash;

class SuperAdminUsersTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        $faker = \Faker\Factory::create();

        $superadmin = new User();
        $superadmin->name = $faker->name;
        $superadmin->email = 'superadmin@example.com';
        $superadmin->is_superadmin = true;
        $superadmin->save();

        $userAuth = UserAuth::create(['email' => $superadmin->email, 'password' => bcrypt('123456'), 'email_verified_at' => now()]);
        $superadmin->user_auth_id = $userAuth->id;
        $superadmin->saveQuietly();

    }

}
