<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skill_requests', function (Blueprint $table) {
            $table->id('Request_ID');
            $table->foreignId('User_ID')->constrained('users', 'User_ID');
            $table->foreignId('Skill_ID')->constrained('skills', 'Skill_ID');
            $table->string('Title');
            $table->text('Description')->nullable();
            $table->string('Status', 20)->default('Open');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_requests');
    }
};
