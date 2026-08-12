<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Deduplicate existing skills by Skill_Title (keep the one with the lowest Skill_ID)
        if (Schema::hasTable('skills')) {
            $duplicates = DB::table('skills')
                ->select('Skill_Title')
                ->groupBy('Skill_Title')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('Skill_Title');

            foreach ($duplicates as $title) {
                $ids = DB::table('skills')
                    ->where('Skill_Title', $title)
                    ->orderBy('Skill_ID')
                    ->pluck('Skill_ID');
                $keepId = $ids->shift();
                DB::table('skills')
                    ->whereIn('Skill_ID', $ids)
                    ->delete();
            }

            Schema::table('skills', function (Blueprint $table) {
                $table->unique('Skill_Title');
            });
        }
    }

    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->dropUnique(['Skill_Title']);
        });
    }
};
