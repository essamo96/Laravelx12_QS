<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('roles')) {
            return;
        }

        if ($this->usersRoleForeignExists()) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users') || ! $this->usersRoleForeignExists()) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
        });
    }

    private function usersRoleForeignExists(): bool
    {
        $database = DB::getDatabaseName();

        $row = DB::selectOne(
            'SELECT 1 AS ok FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_TYPE = ? AND CONSTRAINT_NAME = ?
             LIMIT 1',
            [$database, 'users', 'FOREIGN KEY', 'users_role_id_foreign']
        );

        return $row !== null;
    }
};
