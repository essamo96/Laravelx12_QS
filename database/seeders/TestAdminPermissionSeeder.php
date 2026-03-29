<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestAdminPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $groupId = DB::table('permissions_group')->insertGetId([
            'name' => 'tests',
            'name_ar' => 'Test',
            'name_en' => 'Test',
            'icon' => null,
            'sort' => 999,
            'status' => 1,
            'parent_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (['add', 'view', 'edit', 'delete', 'status'] as $action) {
            DB::table('permissions')->updateOrInsert(
                ['name' => 'admin.tests.' . $action, 'guard_name' => 'admin'],
                ['group_id' => $groupId, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}