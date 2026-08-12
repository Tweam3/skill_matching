<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_skills', function (Blueprint $table) {
            $table->id('Request_Skill_ID');
            $table->foreignId('Request_ID')->constrained('skill_requests', 'Request_ID')->cascadeOnDelete();
            $table->foreignId('Skill_ID')->constrained('skills', 'Skill_ID')->cascadeOnDelete();
            $table->unique(['Request_ID', 'Skill_ID']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_skills');
    }
};
