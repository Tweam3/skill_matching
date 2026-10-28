<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("user_skills", function (Blueprint $table) {
            $table->string("Status", 20)->default("pending")->after("Proficiency");
        });
    }

    public function down(): void
    {
        Schema::table("user_skills", function (Blueprint $table) {
            $table->dropColumn("Status");
        });
    }
};
