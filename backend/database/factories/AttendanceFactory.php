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

        $remarksList = [
            '電車遅延のため',
            '体調不良のため',
            '私用のため早退',
            '客先訪問のため外出',
        ];

        if ($status === 'pending') {
            $submittedAt = $this->faker->dateTime();
            $remarks     = $this->faker->randomElement($remarksList);
        } elseif ($status === 'approved') {
            $submittedAt = $this->faker->dateTime();
            // approved_at は submitted_at の翌日
            $approvedAt  = (clone $submittedAt)->modify('+1 day');
            $remarks     = $this->faker->randomElement($remarksList);
        }

        return [
            'user_id'      => User::factory(),
            'work_date'    => $this->faker->date(),
            'clock_in'     => null,
            'clock_out'    => null,
            'remarks'      => null,
            'status'       => 'normal',
            'submitted_at' => null,
            'approved_at'  => null,
        ];
    }
}
