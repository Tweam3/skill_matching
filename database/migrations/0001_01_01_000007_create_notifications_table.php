<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('Notif_ID');
            $table->foreignId('User_ID')->constrained('users', 'User_ID');
            $table->string('Notif_Type')->nullable();
            $table->text('Message')->nullable();
            $table->string('Status', 20)->default('Unread');
            $table->timestamp('Created_At')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
