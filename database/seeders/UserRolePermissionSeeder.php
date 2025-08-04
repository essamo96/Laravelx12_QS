<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class UserRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. إنشاء رول جديد أو استخدم الموجود
        $role = Role::updateOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'admin'],
            ['status' => 1, 'is_user' => 0]
        );

        // 2. جلب جميع الصلاحيات المرتبطة بـ guard_name = admin
        $permissions = Permission::where('guard_name', 'admin')->get();

        // 3. إعطاء جميع الصلاحيات للرول
        $role->syncPermissions($permissions);

        // 4. إنشاء مستخدم جديد وربطه بالرول (باستخدام role_id بدلًا من role)
        $user = User::updateOrCreate(
            ['email' => 'essam@hotmail.com'],
            [
                'name' => 'Super Admin',
                'username' => 'essam',
                'email_verified_at' => now(),
                'password' => Hash::make('essam 1033'),
                'created_by' => null,
                'role_id' => $role->id,
                'status' => 1,
            ]
        );

        // 5. ربط المستخدم بالرول باستخدام Spatie
        $user->assignRole($role);
    }
}
