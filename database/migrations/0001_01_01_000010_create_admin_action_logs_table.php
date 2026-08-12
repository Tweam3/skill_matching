<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_action_logs', function (Blueprint $table) {
            $table->id('Log_ID');
            $table->unsignedInteger('Admin_ID');
            $table->string('Action');
            $table->text('Details')->nullable();
            $table->timestamp('Created_At')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_action_logs');
    }
};
