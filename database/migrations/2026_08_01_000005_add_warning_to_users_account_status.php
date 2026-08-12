<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN Account_Status ENUM('Active', 'Warning', 'Suspended', 'Banned') NOT NULL DEFAULT 'Active'");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN Account_Status ENUM('Active', 'Suspended', 'Banned') NOT NULL DEFAULT 'Active'");
    }
};
