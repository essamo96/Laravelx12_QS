<?php

namespace App\Services\AdminGenerator;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SchemaReaderService
{
    public function getColumns(string $tableName): Collection
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        return DB::table('INFORMATION_SCHEMA.COLUMNS')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', $tableName)
            ->whereNotIn('COLUMN_NAME', ['id', 'created_at', 'updated_at', 'deleted_at'])
            ->orderBy('ORDINAL_POSITION')
            ->select(['COLUMN_NAME', 'COLUMN_TYPE', 'DATA_TYPE', 'IS_NULLABLE', 'COLUMN_COMMENT'])
            ->get();
    }
}

