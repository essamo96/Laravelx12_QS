<?php

namespace App\Console\Commands;

use App\Services\PermissionGeneratorService;
use Illuminate\Console\Command;

class SyncAdminPermissionsCommand extends Command
{
    protected $signature = 'permissions:sync-admin {--table= : Sync only this table key}';

    protected $description = 'Create permissions from permissions_group + resources/views/admin folders; assign super-admin';

    public function handle(PermissionGeneratorService $service): int
    {
        if ($table = $this->option('table')) {
            $service->syncPermissionsForTable($table);
            $service->assignAllPermissionsToSuperAdmin();
            $this->info("Permissions synced for {$table}.");

            return self::SUCCESS;
        }

        $service->syncAll();
        $this->info('All permissions synced.');

        return self::SUCCESS;
    }
}
