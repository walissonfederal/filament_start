<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public static function list()
    {
        return [
            [
                "name"        => "Administrator",
                "position"    => 1,
                "system"      => 1,
                "permissions" => [],
            ],
            [
                "name"        => "User",
                "position"    => 2,
                "system"      => 1,
                "permissions" => [
                    "filament.admin.pages.dashboard",
                ],
            ],
        ];
    }

    public function run(): void
    {
        $list = self::list();

        foreach ($list as $data) {
            $role = Role::updateOrCreate([
                "name" => $data["name"],
            ], [
                "position" => $data["position"],
                "system"   => $data["system"],
            ]);

            $role->permissions()->syncWithoutDetaching(
                Permission::whereIn("name", $data["permissions"])
                    ->pluck("id")
                    ->toArray()
            );
        }
    }
}
