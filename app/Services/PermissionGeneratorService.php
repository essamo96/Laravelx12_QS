<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionGeneratorService
{
    public function guard(): string
    {
        return (string) config('permission_generator.guard', 'admin');
    }

    public function actions(): array
    {
        return config('permission_generator.actions', ['view', 'add', 'edit', 'delete', 'update', 'status']);
    }

    public function forgetCachedPermissions(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function permissionName(string $table, string $action): string
    {
        $prefix = config('permission_generator.permission_prefix', 'admin');

        return "{$prefix}.{$table}.{$action}";
    }

    public function ensureGroupForTable(string $table): int
    {
        $table = $this->normalizeTableKey($table);

        if (! Schema::hasTable('permissions_group')) {
            throw new \RuntimeException('Table permissions_group does not exist.');
        }

        $existing = DB::table('permissions_group')->where('name', $table)->first();
        if ($existing) {
            return (int) $existing->id;
        }

        $parentId = (int) config('permission_generator.default_group_parent_id', 0);

        return (int) DB::table('permissions_group')->insertGetId([
            'name' => $table,
            'name_ar' => $table,
            'name_en' => Str::headline(str_replace('_', ' ', $table)),
            'icon' => null,
            'color' => 'primary',
            'sort' => 50,
            'status' => 1,
            'parent_id' => $parentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function syncPermissionsForTable(string $table): void
    {
        $table = $this->normalizeTableKey($table);
        $groupId = $this->ensureGroupForTable($table);
        $guard = $this->guard();

        foreach ($this->actions() as $action) {
            $name = $this->permissionName($table, $action);
            $now = now();

            $row = DB::table('permissions')
                ->where('name', $name)
                ->where('guard_name', $guard)
                ->first();

            if ($row) {
                DB::table('permissions')->where('id', $row->id)->update([
                    'group_id' => $groupId,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('permissions')->insert([
                    'name' => $name,
                    'guard_name' => $guard,
                    'group_id' => $groupId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $this->forgetCachedPermissions();
    }

    public function syncFromAdminViewFolders(): array
    {
        $created = [];
        $base = resource_path('views/admin');

        if (! is_dir($base)) {
            return $created;
        }

        $skip = array_flip(config('permission_generator.skip_view_directories', []));

        foreach (File::directories($base) as $dir) {
            $folder = basename($dir);
            if (isset($skip[$folder])) {
                continue;
            }

            $this->syncPermissionsForTable($folder);
            $created[] = $folder;
        }

        return $created;
    }

    public function syncFromPermissionsGroups(): void
    {
        if (! Schema::hasTable('permissions_group')) {
            return;
        }

        $rows = DB::table('permissions_group')->select('name')->orderBy('id')->get();

        foreach ($rows as $row) {
            if (empty($row->name)) {
                continue;
            }
            $this->syncPermissionsForTable((string) $row->name);
        }
    }

    public function syncAll(): void
    {
        $this->syncFromPermissionsGroups();
        $this->syncFromAdminViewFolders();
        $this->assignAllPermissionsToSuperAdmin();
        $this->forgetCachedPermissions();
    }

    public function assignAllPermissionsToSuperAdmin(): void
    {
        $roleName = (string) config('permission_generator.super_admin_role', 'super-admin');
        $guard = $this->guard();

        $role = Role::query()->firstOrCreate(
            ['name' => $roleName, 'guard_name' => $guard],
            ['status' => 1]
        );

        $permissions = Permission::query()->where('guard_name', $guard)->get();
        $role->syncPermissions($permissions);

        $this->forgetCachedPermissions();
    }

    public function assignSuperAdminRole(User $user): void
    {
        $roleName = (string) config('permission_generator.super_admin_role', 'super-admin');
        $guard = $this->guard();

        $role = Role::query()->firstOrCreate(
            ['name' => $roleName, 'guard_name' => $guard],
            ['status' => 1]
        );

        $user->syncRoles([$role]);

        $this->forgetCachedPermissions();
    }

    public function createTableMigrationIfMissing(string $table): ?string
    {
        $table = $this->normalizeTableKey($table);

        if (Schema::hasTable($table)) {
            return null;
        }

        $name = 'create_' . $table . '_table';

        Artisan::call('make:migration', [
            'name' => $name,
            '--create' => $table,
        ]);

        return $name;
    }

    protected function normalizeTableKey(string $table): string
    {
        return Str::snake(str_replace(['-', ' '], '_', $table));
    }
}
