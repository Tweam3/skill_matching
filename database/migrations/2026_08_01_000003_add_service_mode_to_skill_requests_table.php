<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('skill_requests', 'Service_Mode')) {
            return;
        }

        Schema::table('skill_requests', function (Blueprint $table) {
            $table->string('Service_Mode', 20)->default('Remote');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('skill_requests', 'Service_Mode')) {
            return;
        }

        Schema::table('skill_requests', function (Blueprint $table) {
            $table->dropColumn('Service_Mode');
        });
    }
};
