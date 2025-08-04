<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
     public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $id = 1;
        $groups = DB::table('permissions_group')->orderBy('id')->get();

        foreach ($groups as $group) {
            $actions = ($group->parent_id != 0)
                ? ['add', 'view', 'edit', 'delete', 'status']
                : ['view'];

            foreach ($actions as $action) {
                DB::table('permissions')->insert([
                    'id' => $id++,
                    'name' => 'admin.' . $group->name . '.' . $action,
                    'guard_name' => 'admin',
                    'group_id' => $group->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
