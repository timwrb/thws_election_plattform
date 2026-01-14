<?php

use App\Enums\Season;
use App\Models\Awpf;
use App\Models\Fwpm;
use App\Models\ResearchProject;
use App\Models\Semester;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('semesters relationship', function (): void {
    it('can associate an AWPF course with a semester', function (): void {
        $awpf = Awpf::factory()->create();
        $semester = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);

        $awpf->semesters()->attach($semester);

        expect($awpf->semesters)->toHaveCount(1)
            ->and($awpf->semesters->first()->id)->toBe($semester->id);
    });

    it('can associate an FWPM course with multiple semesters', function (): void {
        $fwpm = Fwpm::factory()->create();
        $ws24 = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);
        $ss25 = Semester::query()->create(['year' => 2025, 'season' => Season::Summer]);

        $fwpm->semesters()->attach([$ws24->id, $ss25->id]);

        expect($fwpm->semesters)->toHaveCount(2);
    });

    it('can associate a research project with a semester', function (): void {
        $semester = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);
        $project = ResearchProject::factory()->create([
            'semester_id' => $semester->id,
        ]);

        $project->semesters()->attach($semester);

        expect($project->semesters)->toHaveCount(1)
            ->and($project->semesters->first()->id)->toBe($semester->id);
    });
});

describe('assignToSemester method', function (): void {
    it('assigns course to semester', function (): void {
        $awpf = Awpf::factory()->create();
        $semester = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);

        $awpf->assignToSemester($semester);

        expect($awpf->semesters)->toHaveCount(1)
            ->and($awpf->semesters->first()->id)->toBe($semester->id);
    });

    it('does not duplicate assignment', function (): void {
        $awpf = Awpf::factory()->create();
        $semester = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);

        $awpf->assignToSemester($semester);
        $awpf->assignToSemester($semester); // Call twice

        $awpf->refresh();

        expect($awpf->semesters)->toHaveCount(1);
    });
});

describe('removeFromSemester method', function (): void {
    it('removes course from semester', function (): void {
        $fwpm = Fwpm::factory()->create();
        $semester = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);

        $fwpm->assignToSemester($semester);
        expect($fwpm->semesters)->toHaveCount(1);

        $fwpm->removeFromSemester($semester);

        $fwpm->refresh();
        expect($fwpm->semesters)->toHaveCount(0);
    });
});

describe('isInSemester method', function (): void {
    it('returns true when course is in semester', function (): void {
        $awpf = Awpf::factory()->create();
        $semester = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);

        $awpf->assignToSemester($semester);

        expect($awpf->isInSemester($semester))->toBeTrue();
    });

    it('returns false when course is not in semester', function (): void {
        $awpf = Awpf::factory()->create();
        $semester = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);

        expect($awpf->isInSemester($semester))->toBeFalse();
    });
});

describe('syncSemesters method', function (): void {
    it('syncs semesters with array of IDs', function (): void {
        $awpf = Awpf::factory()->create();
        $ws24 = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);
        $ss25 = Semester::query()->create(['year' => 2025, 'season' => Season::Summer]);
        $ws25 = Semester::query()->create(['year' => 2025, 'season' => Season::Winter]);

        // Initial assignment
        $awpf->assignToSemester($ws24);
        expect($awpf->semesters)->toHaveCount(1);

        // Sync with new semesters
        $awpf->syncSemesters([$ss25->id, $ws25->id]);

        $awpf->refresh();
        expect($awpf->semesters)->toHaveCount(2)
            ->and($awpf->semesters->pluck('id')->toArray())->toContain($ss25->id, $ws25->id)
            ->and($awpf->semesters->pluck('id')->toArray())->not->toContain($ws24->id);
    });

    it('syncs semesters with single semester instance', function (): void {
        $fwpm = Fwpm::factory()->create();
        $ws24 = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);
        $ss25 = Semester::query()->create(['year' => 2025, 'season' => Season::Summer]);

        $fwpm->assignToSemester($ws24);
        $fwpm->assignToSemester($ss25);
        expect($fwpm->semesters)->toHaveCount(2);

        $ws25 = Semester::query()->create(['year' => 2025, 'season' => Season::Winter]);
        $fwpm->syncSemesters($ws25);

        $fwpm->refresh();
        expect($fwpm->semesters)->toHaveCount(1)
            ->and($fwpm->semesters->first()->id)->toBe($ws25->id);
    });
});

describe('forSemester scope', function (): void {
    it('filters courses by semester', function (): void {
        $ws24 = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);
        $ss25 = Semester::query()->create(['year' => 2025, 'season' => Season::Summer]);

        $awpf1 = Awpf::factory()->create();
        $awpf2 = Awpf::factory()->create();
        $awpf3 = Awpf::factory()->create();

        $awpf1->assignToSemester($ws24);
        $awpf2->assignToSemester($ss25);
        $awpf3->assignToSemester($ws24);

        $ws24Courses = Awpf::query()->forSemester($ws24)->get();

        expect($ws24Courses)->toHaveCount(2)
            ->and($ws24Courses->pluck('id')->toArray())->toContain($awpf1->id, $awpf3->id)
            ->and($ws24Courses->pluck('id')->toArray())->not->toContain($awpf2->id);
    });

    it('filters research projects by semester', function (): void {
        $ws24 = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);
        $ss25 = Semester::query()->create(['year' => 2025, 'season' => Season::Summer]);

        $project1 = ResearchProject::factory()->create(['semester_id' => $ws24->id]);
        $project2 = ResearchProject::factory()->create(['semester_id' => $ss25->id]);

        $project1->assignToSemester($ws24);
        $project2->assignToSemester($ss25);

        $ws24Projects = ResearchProject::query()->forSemester($ws24)->get();

        expect($ws24Projects)->toHaveCount(1)
            ->and($ws24Projects->first()->id)->toBe($project1->id);
    });

    it('returns empty collection when no courses in semester', function (): void {
        $semester = Semester::query()->create(['year' => 2024, 'season' => Season::Winter]);

        Fwpm::factory()->count(3)->create();

        $courses = Fwpm::query()->forSemester($semester)->get();

        expect($courses)->toHaveCount(0);
    });
});
