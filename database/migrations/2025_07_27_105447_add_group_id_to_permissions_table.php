<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('permissions_group')) {
            return;
        }

        if (Schema::hasColumn('permissions', 'group_id')) {
            return;
        }

        Schema::table('permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->after('guard_name');
        });

        $database = DB::getDatabaseName();
        $constraint = 'permissions_group_id_foreign';
        $exists = DB::selectOne(
            'SELECT 1 AS ok FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ?
             LIMIT 1',
            [$database, 'permissions', $constraint, 'FOREIGN KEY']
        );

        if ($exists === null) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->foreign('group_id')
                    ->references('id')
                    ->on('permissions_group')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasColumn('permissions', 'group_id')) {
            return;
        }

        $database = DB::getDatabaseName();
        $constraint = 'permissions_group_id_foreign';
        $exists = DB::selectOne(
            'SELECT 1 AS ok FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ?
             LIMIT 1',
            [$database, 'permissions', $constraint, 'FOREIGN KEY']
        );

        if ($exists !== null) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropForeign(['group_id']);
            });
        }

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('group_id');
        });
    }
};
