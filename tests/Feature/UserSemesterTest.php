<?php

use App\Enums\Season;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

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
        Date::setTestNow(Date::create(2024, 10, 15));

        $user = User::factory()->create();
        $current = $user->getCurrentSemester();

        expect($current)->not->toBeNull()
            ->and($current->id)->toBe($ws24->id);
    });

    it('returns null when current semester does not exist', function (): void {
        Date::setTestNow(Date::create(2024, 10, 15));

        $user = User::factory()->create();
        $current = $user->getCurrentSemester();

        expect($current)->toBeNull();
    });
});

describe('getSemesterNumber method', function (): void {
    it('calculates semester number correctly', function (): void {
        $ws23 = Semester::query()->create(['year' => 2023, 'season' => Season::Winter]);
        Semester::query()->create(['year' => 2025, 'season' => Season::Winter]);
        $user = User::factory()->create(['start_semester_id' => $ws23->id]);
        Date::setTestNow(Date::create(2025, 10, 15));

        $semesterNumber = $user->getSemesterNumber();

        expect($semesterNumber)->toBe(5);
    });

    it('returns 1 for first semester', function (): void {
        $ws24 = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);
        $user = User::factory()->create(['start_semester_id' => $ws24->id]);
        Date::setTestNow(Date::create(2024, 10, 15));

        $semesterNumber = $user->getSemesterNumber();

        expect($semesterNumber)->toBe(1);
    });

    it('returns null when user has no start semester', function (): void {
        Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);
        $user = User::factory()->create(['start_semester_id' => null]);
        Date::setTestNow(Date::create(2024, 10, 15));

        $semesterNumber = $user->getSemesterNumber();

        expect($semesterNumber)->toBeNull();
    });

    it('returns null when current semester does not exist', function (): void {
        $ws23 = Semester::query()->create(['year' => 2023, 'season' => Season::Winter]);
        $user = User::factory()->create(['start_semester_id' => $ws23->id]);
        Date::setTestNow(Date::create(2026, 10, 15));

        $semesterNumber = $user->getSemesterNumber();

        expect($semesterNumber)->toBeNull();
    });

    it('calculates correctly for summer semester start', function (): void {
        $ss23 = Semester::query()->create(['year' => 2023, 'season' => Season::Summer]);
        Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);
        $user = User::factory()->create(['start_semester_id' => $ss23->id]);
        Date::setTestNow(Date::create(2024, 10, 15));

        $semesterNumber = $user->getSemesterNumber();

        expect($semesterNumber)->toBe(4);
    });

    it('calculates correctly across multiple years', function (): void {
        $ws20 = Semester::query()->create(['year' => 2020, 'season' => Season::Winter]);
        Semester::query()->create(['year' => 2025, 'season' => Season::Winter]);
        $user = User::factory()->create(['start_semester_id' => $ws20->id]);
        Date::setTestNow(Date::create(2025, 10, 15));

        $semesterNumber = $user->getSemesterNumber();

        expect($semesterNumber)->toBe(11);
    });
});
