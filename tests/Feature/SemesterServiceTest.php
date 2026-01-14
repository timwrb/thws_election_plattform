<?php

use App\Enums\Season;
use App\Models\Semester;
use App\Models\User;
use App\Services\SemesterService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = app(SemesterService::class);
});

describe('getCurrentSemester', function (): void {
    it('returns winter semester for October', function (): void {
        // Create semesters
        $ws24 = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);
        $ss25 = Semester::query()->create(['year' => 2025, 'season' => Season::Summer]);

        // Mock date to October 15, 2024
        Carbon\Carbon::setTestNow(Carbon\Carbon::create(2024, 10, 15));

        $current = $this->service->getCurrentSemester();

        expect($current)->not->toBeNull()
            ->and($current->id)->toBe($ws24->id)
            ->and($current->season)->toBe(Season::Winter)
            ->and($current->year)->toBe(2024);
    });

    it('returns winter semester for January', function (): void {
        // Create semesters
        $ws24 = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);
        $ss25 = Semester::query()->create(['year' => 2025, 'season' => Season::Summer]);

        // Mock date to January 15, 2025 (belongs to WS24/25)
        Carbon\Carbon::setTestNow(Carbon\Carbon::create(2025, 1, 15));

        $current = $this->service->getCurrentSemester();

        expect($current)->not->toBeNull()
            ->and($current->id)->toBe($ws24->id)
            ->and($current->season)->toBe(Season::Winter)
            ->and($current->year)->toBe(2024);
    });

    it('returns summer semester for April', function (): void {
        $ss25 = Semester::query()->create(['year' => 2025, 'season' => Season::Summer]);

        // Mock date to April 1, 2025
        Carbon\Carbon::setTestNow(Carbon\Carbon::create(2025, 4, 1));

        $current = $this->service->getCurrentSemester();

        expect($current)->not->toBeNull()
            ->and($current->id)->toBe($ss25->id)
            ->and($current->season)->toBe(Season::Summer)
            ->and($current->year)->toBe(2025);
    });

    it('returns summer semester for September', function (): void {
        $ss25 = Semester::query()->create(['year' => 2025, 'season' => Season::Summer]);

        // Mock date to September 30, 2025
        Carbon\Carbon::setTestNow(Carbon\Carbon::create(2025, 9, 30));

        $current = $this->service->getCurrentSemester();

        expect($current)->not->toBeNull()
            ->and($current->id)->toBe($ss25->id)
            ->and($current->season)->toBe(Season::Summer)
            ->and($current->year)->toBe(2025);
    });

    it('returns null when semester does not exist in database', function (): void {
        // Mock date to October 2024 but don't create semester
        Carbon\Carbon::setTestNow(Carbon\Carbon::create(2024, 10, 15));

        $current = $this->service->getCurrentSemester();

        expect($current)->toBeNull();
    });
});

describe('getSemestersBetween', function (): void {
    it('calculates zero semesters for same semester', function (): void {
        $ws24 = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);

        $count = $this->service->getSemestersBetween($ws24, $ws24);

        expect($count)->toBe(0);
    });

    it('calculates one semester between SS and WS of same year', function (): void {
        $ws24 = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);
        $ss24 = Semester::query()->create(['year' => 2024, 'season' => Season::Summer]);

        $count = $this->service->getSemestersBetween($ss24, $ws24);

        // SS24 (Apr-Sep 2024) -> WS24 (Oct 2024-Mar 2025) = 1 step forward
        expect($count)->toBe(1);
    });

    it('calculates negative semester between WS and SS of same year', function (): void {
        $ws24 = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);
        $ss24 = Semester::query()->create(['year' => 2024, 'season' => Season::Summer]);

        $count = $this->service->getSemestersBetween($ws24, $ss24);

        // WS24 (Oct 2024-Mar 2025) -> SS24 (Apr-Sep 2024) = 1 step backward
        expect($count)->toBe(-1);
    });

    it('calculates semesters between different years', function (): void {
        $ws23 = Semester::query()->create(['year' => 2023, 'season' => Season::Winter]);
        $ws25 = Semester::query()->create(['year' => 2025, 'season' => Season::Winter]);

        $count = $this->service->getSemestersBetween($ws23, $ws25);

        // WS23 -> SS24 -> WS24 -> SS25 -> WS25 = 4 semesters
        expect($count)->toBe(4);
    });

    it('calculates semesters from summer to winter across years', function (): void {
        $ss23 = Semester::query()->create(['year' => 2023, 'season' => Season::Summer]);
        $ws24 = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);

        $count = $this->service->getSemestersBetween($ss23, $ws24);

        // SS23 -> WS23 -> SS24 -> WS24 = 3 semesters
        expect($count)->toBe(3);
    });
});

describe('calculateSemesterNumber', function (): void {
    it('calculates semester number correctly', function (): void {
        $ws23 = Semester::query()->create(['year' => 2023, 'season' => Season::Winter]);
        $ws25 = Semester::query()->create(['year' => 2025, 'season' => Season::Winter]);

        $user = User::factory()->create(['start_semester_id' => $ws23->id]);

        // Mock current date to be in WS25
        Carbon\Carbon::setTestNow(Carbon\Carbon::create(2025, 10, 15));

        $semesterNumber = $this->service->calculateSemesterNumber($user);

        // Started in WS23, now in WS25 = 5th semester
        expect($semesterNumber)->toBe(5);
    });

    it('returns null when user has no start semester', function (): void {
        $user = User::factory()->create(['start_semester_id' => null]);

        $semesterNumber = $this->service->calculateSemesterNumber($user);

        expect($semesterNumber)->toBeNull();
    });

    it('returns null when current semester does not exist', function (): void {
        $ws23 = Semester::query()->create(['year' => 2023, 'season' => Season::Winter]);
        $user = User::factory()->create(['start_semester_id' => $ws23->id]);

        // Mock current date to non-existent semester
        Carbon\Carbon::setTestNow(Carbon\Carbon::create(2026, 10, 15));

        $semesterNumber = $this->service->calculateSemesterNumber($user);

        expect($semesterNumber)->toBeNull();
    });

    it('returns 1 for first semester', function (): void {
        $ws24 = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);
        $user = User::factory()->create(['start_semester_id' => $ws24->id]);

        // Mock current date to be in WS24 (same as start)
        Carbon\Carbon::setTestNow(Carbon\Carbon::create(2024, 10, 15));

        $semesterNumber = $this->service->calculateSemesterNumber($user);

        expect($semesterNumber)->toBe(1);
    });
});

describe('semester status checks', function (): void {
    beforeEach(function (): void {
        $this->ws23 = Semester::query()->create(['year' => 2023, 'season' => Season::Winter]);
        $this->ss24 = Semester::query()->create(['year' => 2024, 'season' => Season::Summer]);
        $this->ws24 = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);
        $this->ss25 = Semester::query()->create(['year' => 2025, 'season' => Season::Summer]);

        // Set current date to WS24
        Carbon\Carbon::setTestNow(Carbon\Carbon::create(2024, 10, 15));
    });

    it('identifies past semester correctly', function (): void {
        expect($this->service->isPastSemester($this->ws23))->toBeTrue()
            ->and($this->service->isPastSemester($this->ss24))->toBeTrue()
            ->and($this->service->isPastSemester($this->ws24))->toBeFalse();
    });

    it('identifies future semester correctly', function (): void {
        expect($this->service->isFutureSemester($this->ss25))->toBeTrue()
            ->and($this->service->isFutureSemester($this->ws24))->toBeFalse()
            ->and($this->service->isFutureSemester($this->ws23))->toBeFalse();
    });

    it('identifies current semester correctly', function (): void {
        expect($this->service->isCurrentSemester($this->ws24))->toBeTrue()
            ->and($this->service->isCurrentSemester($this->ws23))->toBeFalse()
            ->and($this->service->isCurrentSemester($this->ss25))->toBeFalse();
    });
});

describe('getAllSemestersOrdered', function (): void {
    it('returns semesters in chronological order', function (): void {
        $ss24 = Semester::query()->create(['year' => 2024, 'season' => Season::Summer]);
        $ws24 = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);
        $ss25 = Semester::query()->create(['year' => 2025, 'season' => Season::Summer]);
        $ws23 = Semester::query()->create(['year' => 2023, 'season' => Season::Winter]);

        $semesters = $this->service->getAllSemestersOrdered();

        expect($semesters)->toHaveCount(4)
            ->and($semesters[0]->id)->toBe($ws23->id)
            ->and($semesters[1]->id)->toBe($ss24->id)
            ->and($semesters[2]->id)->toBe($ws24->id)
            ->and($semesters[3]->id)->toBe($ss25->id);
    });
});
