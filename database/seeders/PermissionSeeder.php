<?php

namespace Database\Seeders;

use App\Services\Permissions\SyncPermissionsService;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        SyncPermissionsService::handle();
    }
}
