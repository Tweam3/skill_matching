<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_assignments', function (Blueprint $table) {
            $table->id('Assignment_ID');
            $table->foreignId('Request_ID')->constrained('skill_requests', 'Request_ID');
            $table->foreignId('User_ID')->constrained('users', 'User_ID');
            $table->string('Status', 20)->default('Pending');
            $table->timestamp('Created_At')->useCurrent();
            $table->timestamp('Responded_At')->nullable();
            $table->timestamp('Status_Updated_At')->nullable();
            $table->timestamp('Completed_At')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_assignments');
    }
};
