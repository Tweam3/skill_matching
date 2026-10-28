<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("skill_endorsements", function (Blueprint $table) {
            $table->id("Endorsement_ID");
            $table->foreignId("Endorser_ID")->constrained("users", "User_ID")->cascadeOnDelete();
            $table->foreignId("User_Skill_ID")->constrained("user_skills", "User_Skill_ID")->cascadeOnDelete();
            $table->timestamps();
            $table->unique(["Endorser_ID", "User_Skill_ID"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("skill_endorsements");
    }
};
