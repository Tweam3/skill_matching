<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['Email' => 'admin@campus.local'],
            [
                'Full_Name' => 'System Admin',
                'Password_Hash' => '$2y$10$khhNF1iQ/O0iFDS8qFZx.ud8dn86KPOw2vII4VfR8mSk9JeAT0MH6',
                'Role' => 'Admin',
                'Is_Verified' => true,
            ]
        );
        DB::table('skills')->insertOrIgnore([
            ['Skill_Title' => 'PHP', 'Category' => 'Programming', 'Subcategory' => 'Backend'],
            ['Skill_Title' => 'JavaScript', 'Category' => 'Programming', 'Subcategory' => 'Frontend'],
            ['Skill_Title' => 'Python', 'Category' => 'Programming', 'Subcategory' => 'Backend'],
            ['Skill_Title' => 'Java', 'Category' => 'Programming', 'Subcategory' => 'Backend'],
            ['Skill_Title' => 'MySQL', 'Category' => 'Database', 'Subcategory' => 'Databases'],
            ['Skill_Title' => 'HTML/CSS', 'Category' => 'Web Development', 'Subcategory' => null],
            ['Skill_Title' => 'React', 'Category' => 'Programming', 'Subcategory' => 'Frontend'],
            ['Skill_Title' => 'Node.js', 'Category' => 'Backend', 'Subcategory' => 'Backend'],
            ['Skill_Title' => 'Graphic Design', 'Category' => 'Design', 'Subcategory' => null],
            ['Skill_Title' => 'Public Speaking', 'Category' => 'Communication', 'Subcategory' => null],
            ['Skill_Title' => 'Flutter', 'Category' => 'Mobile Development', 'Subcategory' => 'Mobile'],
            ['Skill_Title' => 'AWS', 'Category' => 'Cloud', 'Subcategory' => 'Cloud'],
            ['Skill_Title' => 'SEO', 'Category' => 'Marketing', 'Subcategory' => null],
            ['Skill_Title' => 'Content Writing', 'Category' => 'Writing', 'Subcategory' => null],
        ]);
        $this->call([
            SkillSeeder::class,
        ]);
    }
}
