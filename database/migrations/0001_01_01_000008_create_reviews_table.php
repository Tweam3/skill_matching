<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id('Review_ID');
            $table->foreignId('Reviewed_User_ID')->constrained('users', 'User_ID');
            $table->foreignId('Reviewer_ID')->constrained('users', 'User_ID');
            $table->foreignId('Request_ID')->constrained('skill_requests', 'Request_ID');
            $table->tinyInteger('Rating');
            $table->text('Comment')->nullable();
            $table->timestamp('Created_At')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
