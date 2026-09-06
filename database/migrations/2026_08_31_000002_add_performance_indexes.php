<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_skills', function (Blueprint $table) {
            $table->index('User_ID');
            $table->index('Skill_ID');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->index('Request_ID');
            $table->index('Matched_User_ID');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index('Reviewed_User_ID');
            $table->index('Created_At');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->index('Reported_User_ID');
            $table->index('Status');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index('User_ID');
            $table->index('Status');
        });

        Schema::table('admin_action_logs', function (Blueprint $table) {
            $table->index('User_ID');
        });
    }

    public function down(): void
    {
        Schema::table('user_skills', function (Blueprint $table) {
            $table->dropIndex(['User_ID']);
            $table->dropIndex(['Skill_ID']);
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropIndex(['Request_ID']);
            $table->dropIndex(['Matched_User_ID']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['Reviewed_User_ID']);
            $table->dropIndex(['Created_At']);
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex(['Reported_User_ID']);
            $table->dropIndex(['Status']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['User_ID']);
            $table->dropIndex(['Status']);
        });

        Schema::table('admin_action_logs', function (Blueprint $table) {
            $table->dropIndex(['User_ID']);
        });
    }
};
