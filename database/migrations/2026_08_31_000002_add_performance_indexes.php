<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEXES = [
        'user_skills' => ['User_ID', 'Skill_ID'],
        'matches' => ['Request_ID', 'Matched_User_ID'],
        'reviews' => ['Reviewed_User_ID', 'Created_At'],
        'reports' => ['Reported_User_ID', 'Status'],
        'notifications' => ['User_ID', 'Status'],
        'admin_action_logs' => ['Admin_ID'],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $columns) {
            foreach ($columns as $column) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                $indexName = $table.'_'.$column.'_index';

                if (! $this->indexExists($table, $indexName)) {
                    Schema::table($table, function (Blueprint $table) use ($column, $indexName) {
                        $table->index($column, $indexName);
                    });
                }
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $columns) {
            foreach ($columns as $column) {
                $indexName = $table.'_'.$column.'_index';

                if ($this->indexExists($table, $indexName)) {
                    Schema::table($table, function (Blueprint $table) use ($indexName) {
                        $table->dropIndex($indexName);
                    });
                }
            }
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            $result = DB::select(
                'SELECT 1 FROM pg_indexes WHERE schemaname = ? AND tablename = ? AND indexname = ?',
                ['public', $table, $indexName]
            );
        } else {
            $result = DB::select(
                'SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?',
                [$indexName]
            );
        }

        return ! empty($result);
    }
};
