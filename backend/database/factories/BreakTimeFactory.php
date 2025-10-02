<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakTimeFactory extends Factory
{
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('12:00', '13:00');
        $end = (clone $start)->modify('+1 hour');

        return [
            'attendance_id' => Attendance::factory(),
            'break_start' => $start,
            'break_end' => $end,
        ];
    }
}
