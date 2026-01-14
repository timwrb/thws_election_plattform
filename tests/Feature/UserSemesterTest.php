<?php

use App\Enums\Season;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('startSemester relationship', function (): void {
    it('returns user start semester', function (): void {
        $semester = Semester::query()->create(['year' => 2023, 'season' => Season::Winter]);
        $user = User::factory()->create(['start_semester_id' => $semester->id]);

        expect($user->startSemester)->not->toBeNull()
            ->and($user->startSemester->id)->toBe($semester->id)
            ->and($user->startSemester->year)->toBe(2023)
            ->and($user->startSemester->season)->toBe(Season::Winter);
    });

    it('returns null when user has no start semester', function (): void {
        $user = User::factory()->create(['start_semester_id' => null]);

        expect($user->startSemester)->toBeNull();
    });
});

describe('getCurrentSemester method', function (): void {
    it('returns current semester based on date', function (): void {
        $ws24 = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);

        // Mock date to be in WS24
        Carbon\Carbon::setTestNow(Carbon\Carbon::create(2024, 10, 15));

        $user = User::factory()->create();

        $current = $user->getCurrentSemester();

        expect($current)->not->toBeNull()
            ->and($current->id)->toBe($ws24->id);
    });

    it('returns null when current semester does not exist', function (): void {
        // Mock date but don't create semester
        Carbon\Carbon::setTestNow(Carbon\Carbon::create(2024, 10, 15));

        $user = User::factory()->create();

        $current = $user->getCurrentSemester();

        expect($current)->toBeNull();
    });
});

describe('getSemesterNumber method', function (): void {
    it('calculates semester number correctly', function (): void {
        $ws23 = Semester::query()->create(['year' => 2023, 'season' => Season::Winter]);
        $ws25 = Semester::query()->create(['year' => 2025, 'season' => Season::Winter]);

        $user = User::factory()->create(['start_semester_id' => $ws23->id]);

        // Mock current date to be in WS25
        Carbon\Carbon::setTestNow(Carbon\Carbon::create(2025, 10, 15));

        $semesterNumber = $user->getSemesterNumber();

        // WS23 -> SS24 -> WS24 -> SS25 -> WS25 = 5th semester
        expect($semesterNumber)->toBe(5);
    });

    it('returns 1 for first semester', function (): void {
        $ws24 = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);
        $user = User::factory()->create(['start_semester_id' => $ws24->id]);

        // Mock current date to be in WS24 (same as start)
        Carbon\Carbon::setTestNow(Carbon\Carbon::create(2024, 10, 15));

        $semesterNumber = $user->getSemesterNumber();

        expect($semesterNumber)->toBe(1);
    });

    it('returns null when user has no start semester', function (): void {
        Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);
        $user = User::factory()->create(['start_semester_id' => null]);

        Carbon\Carbon::setTestNow(Carbon\Carbon::create(2024, 10, 15));

        $semesterNumber = $user->getSemesterNumber();

        expect($semesterNumber)->toBeNull();
    });

    it('returns null when current semester does not exist', function (): void {
        $ws23 = Semester::query()->create(['year' => 2023, 'season' => Season::Winter]);
        $user = User::factory()->create(['start_semester_id' => $ws23->id]);

        // Mock date to non-existent semester
        Carbon\Carbon::setTestNow(Carbon\Carbon::create(2026, 10, 15));

        $semesterNumber = $user->getSemesterNumber();

        expect($semesterNumber)->toBeNull();
    });

    it('calculates correctly for summer semester start', function (): void {
        $ss23 = Semester::query()->create(['year' => 2023, 'season' => Season::Summer]);
        $ws24 = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);

        $user = User::factory()->create(['start_semester_id' => $ss23->id]);

        // Mock current date to be in WS24
        Carbon\Carbon::setTestNow(Carbon\Carbon::create(2024, 10, 15));

        $semesterNumber = $user->getSemesterNumber();

        // SS23 -> WS23 -> SS24 -> WS24 = 4th semester
        expect($semesterNumber)->toBe(4);
    });

    it('calculates correctly across multiple years', function (): void {
        $ws20 = Semester::query()->create(['year' => 2020, 'season' => Season::Winter]);
        $ws25 = Semester::query()->create(['year' => 2025, 'season' => Season::Winter]);

        $user = User::factory()->create(['start_semester_id' => $ws20->id]);

        // Mock current date to be in WS25
        Carbon\Carbon::setTestNow(Carbon\Carbon::create(2025, 10, 15));

        $semesterNumber = $user->getSemesterNumber();

        // WS20 to WS25 = 11 semesters (5 years * 2 + 1)
        expect($semesterNumber)->toBe(11);
    });
});
