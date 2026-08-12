<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id('Message_ID');
            $table->foreignId('Sender_ID')->constrained('users', 'User_ID');
            $table->foreignId('Receiver_ID')->constrained('users', 'User_ID');
            $table->text('Message_Text');
            $table->timestamp('Sent_At')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
