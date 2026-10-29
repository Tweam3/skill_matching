<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouncilSkillSeeder extends Seeder
{
    public function run(): void
    {
        $mapping = [
            // CSC - Computer Studies Council (Technology, Programming, Data, AI, etc.)
            'Technology' => 'CSC',
            'Cognitive & Thinking' => 'CSC',

            // BIT - Business Information Technology / Industrial Technology (Design, Manufacturing, Engineering, CAD)
            'Creative & Artistic' => 'BIT',

            // HBM - Hotel and Business Management (Communication, Business, Service, Leadership)
            'Communication' => 'HBM',
            'Social & Interpersonal' => 'HBM',

            // EDUC - Education (Teaching, Research, etc.)
            // Unaffiliated - everything else
        ];

        foreach ($mapping as $category => $council) {
            DB::table('skills')
                ->where('Category', $category)
                ->update(['Council' => $council]);
        }

        // Set remaining uncategorized skills to Unaffiliated
        DB::table('skills')
            ->whereNull('Council')
            ->update(['Council' => 'Unaffiliated']);
    }
}