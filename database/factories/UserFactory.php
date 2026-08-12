<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'Full_Name' => fake()->name(),
            'Email' => fake()->unique()->safeEmail(),
            'Password_Hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'Role' => 'Student',
            'Is_Verified' => true,
            'Avg_Rating' => fake()->randomFloat(2, 0, 5),
            'Total_Completed' => fake()->numberBetween(0, 20),
            'Account_Status' => 'Active',
            'Warning_Count' => 0,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'Is_Verified' => false,
        ]);
    }
}
