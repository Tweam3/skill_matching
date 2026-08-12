<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('skill_requests', function (Blueprint $table) {
            $table->enum('Status', ['Open', 'Assigned', 'Completed', 'Cancelled', 'Pending'])->default('Open')->change();
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('skill_requests', function (Blueprint $table) {
            $table->enum('Status', ['Open', 'Assigned', 'Completed', 'Cancelled'])->default('Open')->change();
        });
    }
};
