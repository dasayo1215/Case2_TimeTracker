<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'work_date' => $this->faker->date(),
            'clock_in' => $this->faker->dateTimeBetween('08:00', '10:00'),
            'clock_out' => $this->faker->dateTimeBetween('17:00', '20:00'),
            'remarks' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement(['draft', 'pending', 'approved']),
            'submitted_at' => $this->faker->optional()->dateTime(),
            'approved_at' => $this->faker->optional()->dateTime(),
        ];
    }
}
