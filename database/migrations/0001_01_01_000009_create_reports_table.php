<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id('Report_ID');
            $table->foreignId('Reporter_ID')->constrained('users', 'User_ID');
            $table->foreignId('Reported_User_ID')->constrained('users', 'User_ID');
            $table->foreignId('Request_ID')->nullable()->constrained('skill_requests', 'Request_ID');
            $table->text('Reason');
            $table->enum('Status', ['Pending', 'Dismissed', 'Action_Taken'])->default('Pending');
            $table->foreignId('Admin_ID')->nullable()->constrained('users', 'User_ID');
            $table->text('Admin_Note')->nullable();
            $table->timestamp('Created_At')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
