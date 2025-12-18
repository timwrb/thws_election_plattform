<?php

namespace Database\Seeders;

use App\Enums\DayOfWeek;
use App\Models\Fwpm;
use Illuminate\Database\Seeder;

class FwpmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Fwpm::factory()
            ->count(10)
            ->hasSchedules(2, function () {
                return [
                    'day_of_week' => fake()->randomElement([
                        DayOfWeek::Monday,
                        DayOfWeek::Tuesday,
                        DayOfWeek::Wednesday,
                        DayOfWeek::Thursday,
                        DayOfWeek::Friday,
                    ]),
                    'start_time' => fake()->numberBetween(8, 16).':00:00',
                    'duration_minutes' => fake()->randomElement([90, 120, 180]),
                ];
            })
            ->create();

        Fwpm::factory()->create([
            'name' => 'Mobile Application Development',
        ])->schedules()->createMany([
            [
                'day_of_week' => DayOfWeek::Tuesday,
                'start_time' => '14:00:00',
                'duration_minutes' => 240,
            ],
            [
                'day_of_week' => DayOfWeek::Thursday,
                'start_time' => '14:00:00',
                'duration_minutes' => 240,
            ],
        ]);
    }
}
