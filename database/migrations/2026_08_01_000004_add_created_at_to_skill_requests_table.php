<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('skill_requests', 'Created_At')) {
            Schema::table('skill_requests', function (Blueprint $table) {
                $table->timestamp('Created_At')->useCurrent()->after('Status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('skill_requests', 'Created_At')) {
            Schema::table('skill_requests', function (Blueprint $table) {
                $table->dropColumn('Created_At');
            });
        }
    }
};
