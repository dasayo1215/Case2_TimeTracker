<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        $status = $this->faker->randomElement(['normal', 'pending', 'approved']);

        $submittedAt = null;
        $approvedAt  = null;
        $remarks     = null;

        if ($status === 'pending') {
            $submittedAt = $this->faker->dateTime();
            $remarks     = $this->faker->sentence(); // 必須
        } elseif ($status === 'approved') {
            $submittedAt = $this->faker->dateTime();
            // approved_at は submitted_at の翌日
            $approvedAt  = (clone $submittedAt)->modify('+1 day');
            $remarks     = $this->faker->sentence(); // 必須
        }

        return [
            'user_id'      => User::factory(),
            'work_date'    => $this->faker->date(),
            'clock_in'     => $this->faker->dateTime(),
            'clock_out'    => $this->faker->dateTime(),
            'remarks'      => $remarks,
            'status'       => $status,
            'submitted_at' => $submittedAt,
            'approved_at'  => $approvedAt,
        ];
    }
}
