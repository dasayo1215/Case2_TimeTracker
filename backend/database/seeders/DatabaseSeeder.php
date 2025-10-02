<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DevSeeder::class);
    }
}
