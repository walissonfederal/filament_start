<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate([
            "name"  => "Admin",
            "email" => "admin@admin.com",
        ], [
            "password" => bcrypt(env("ADMIN_PASSWORD")),
            "avatar"   => null,
            "system"   => 1,
        ]);

        $admin->roles()->syncWithoutDetaching(
            Role::where("name", "Administrator")
                ->pluck("id")
                ->toArray()
        );
    }
}
