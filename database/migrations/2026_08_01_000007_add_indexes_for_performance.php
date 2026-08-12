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
            $table->index(['Status']);
            $table->index(['Created_At']);
            $table->index(['Service_Mode']);
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->index(['Matched_At']);
        });

        Schema::table('request_assignments', function (Blueprint $table) {
            $table->index(['Status']);
            $table->index(['Created_At']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['Account_Status']);
            $table->index(['Warning_Count']);
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->index(['Category']);
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('skill_requests', function (Blueprint $table) {
            $table->dropIndex(['Status']);
            $table->dropIndex(['Created_At']);
            $table->dropIndex(['Service_Mode']);
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropIndex(['Matched_At']);
        });

        Schema::table('request_assignments', function (Blueprint $table) {
            $table->dropIndex(['Status']);
            $table->dropIndex(['Created_At']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['Account_Status']);
            $table->dropIndex(['Warning_Count']);
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->dropIndex(['Category']);
        });
    }
};
