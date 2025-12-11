<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WorkingHoursFactory extends Factory
{
    public function definition(): array
    {
        return [
            'day' => null, // رح نملأه بالسييدر
            'open_at' => '09:00',
            'close_at' => '17:00',
            // 'is_open' => true,
            // 'is_24' => false,
        ];
    }
}
