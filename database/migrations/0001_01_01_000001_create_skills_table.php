<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id('Skill_ID');
            $table->string('Skill_Title');
            $table->string('Category')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skills');
    }
};
