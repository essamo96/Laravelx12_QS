<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\PermissionGeneratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(PermissionGeneratorService::class);
        $guard = $service->guard();
        $roleName = (string) config('permission_generator.super_admin_role', 'super-admin');

        $role = Role::query()->firstOrCreate(
            ['name' => $roleName, 'guard_name' => $guard]
        );

        if (\Illuminate\Support\Facades\Schema::hasColumn('roles', 'status')) {
            $role->forceFill(['status' => 1])->save();
        }

        $user = User::query()->updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'username' => 'super_admin',
                'email_verified_at' => now(),
                'password' => Hash::make('12345678'),
                'created_by' => null,
                'role_id' => $role->id,
                'status' => 1,
            ]
        );

        $user->syncRoles([$role]);

        $service->forgetCachedPermissions();
    }
}
