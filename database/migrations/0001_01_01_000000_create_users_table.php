<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('User_ID');
            $table->string('Full_Name');
            $table->string('Email')->unique();
            $table->string('Password_Hash');
            $table->string('Role', 20)->default('Student');
            $table->timestamp('Created_At')->useCurrent();
            $table->decimal('Avg_Rating', 3, 2)->default(0.00);
            $table->integer('Total_Completed')->default(0);
            $table->boolean('Is_Verified')->default(false);
            $table->string('Account_Status', 20)->default('Active');
            $table->integer('Warning_Count')->default(0);
            $table->text('Rejection_Reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
