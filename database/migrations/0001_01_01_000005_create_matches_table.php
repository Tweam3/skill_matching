<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id('Match_ID');
            $table->foreignId('Matched_User_ID')->constrained('users', 'User_ID');
            $table->foreignId('Request_ID')->constrained('skill_requests', 'Request_ID');
            $table->decimal('Match_Score', 5, 2)->nullable();
            $table->timestamp('Matched_At')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
