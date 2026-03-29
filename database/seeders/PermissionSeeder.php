<?php

namespace Database\Seeders;

use App\Services\PermissionGeneratorService;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PermissionsGroupSeeder::class);

        app(PermissionGeneratorService::class)->syncAll();
    }
}
