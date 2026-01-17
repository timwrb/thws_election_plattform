<?php

use App\Enums\EnrollmentStatus;
use App\Enums\EnrollmentType;
use App\Events\Enrollment\EnrollmentConfirmed;
use App\Events\Enrollment\EnrollmentCreated;
use App\Events\Enrollment\EnrollmentRejected;
use App\Events\Enrollment\EnrollmentWithdrawn;
use App\Events\Enrollment\PriorityChoicesRegistered;
use App\Exceptions\Enrollment\CapacityExceededException;
use App\Exceptions\Enrollment\DuplicateEnrollmentException;
use App\Exceptions\Enrollment\InvalidEnrollmentStatusException;
use App\Models\Awpf;
use App\Models\Fwpm;
use App\Models\ResearchProject;
use App\Models\Semester;
use App\Models\User;
use App\Models\UserSelection;
use App\Services\EnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = app(EnrollmentService::class);
    $this->user = User::factory()->create();
    $this->semester = Semester::factory()->year(2024)->winter()->create();
});

describe('enrollInResearchProject', function (): void {
    it('creates enrollment for research project with pending status', function (): void {
        Event::fake();

        $project = ResearchProject::factory()->create(['max_students' => 5]);

        $enrollment = $this->service->enrollInResearchProject(
            $this->user,
            $project,
            $this->semester
        );

        expect($enrollment)
            ->toBeInstanceOf(UserSelection::class)
            ->user_id->toBe($this->user->id)
            ->semester_id->toBe($this->semester->id)
            ->elective_type->toBe(ResearchProject::class)
            ->elective_choice_id->toBe($project->id)
            ->status->toBe(EnrollmentStatus::Pending)
            ->enrollment_type->toBe(EnrollmentType::Direct)
            ->parent_elective_choice_id->toBeNull();

        Event::assertDispatched(EnrollmentCreated::class, fn (EnrollmentCreated $event): bool => $event->enrollment->id === $enrollment->id);
    });

    it('throws exception when project is at capacity', function (): void {
        $project = ResearchProject::factory()->create(['max_students' => 1]);

        UserSelection::query()->create([
            'user_id' => User::factory()->create()->id,
            'semester_id' => $this->semester->id,
            'elective_type' => ResearchProject::class,
            'elective_choice_id' => $project->id,
            'status' => EnrollmentStatus::Confirmed,
            'enrollment_type' => EnrollmentType::Direct,
        ]);

        expect(fn () => $this->service->enrollInResearchProject(
            $this->user,
            $project,
            $this->semester
        ))->toThrow(CapacityExceededException::class);
    });

    it('throws exception when user is already enrolled', function (): void {
        $project = ResearchProject::factory()->create(['max_students' => 5]);

        UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => ResearchProject::class,
            'elective_choice_id' => $project->id,
            'status' => EnrollmentStatus::Pending,
            'enrollment_type' => EnrollmentType::Direct,
        ]);

        expect(fn () => $this->service->enrollInResearchProject(
            $this->user,
            $project,
            $this->semester
        ))->toThrow(DuplicateEnrollmentException::class);
    });

    it('allows enrollment when previous enrollment was withdrawn', function (): void {
        $project = ResearchProject::factory()->create(['max_students' => 5]);

        UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => ResearchProject::class,
            'elective_choice_id' => $project->id,
            'status' => EnrollmentStatus::Withdrawn,
            'enrollment_type' => EnrollmentType::Direct,
        ]);

        $enrollment = $this->service->enrollInResearchProject(
            $this->user,
            $project,
            $this->semester
        );

        expect($enrollment)->toBeInstanceOf(UserSelection::class);
    });

    it('allows enrollment when previous enrollment was rejected', function (): void {
        $project = ResearchProject::factory()->create(['max_students' => 5]);

        UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => ResearchProject::class,
            'elective_choice_id' => $project->id,
            'status' => EnrollmentStatus::Rejected,
            'enrollment_type' => EnrollmentType::Direct,
        ]);

        $enrollment = $this->service->enrollInResearchProject(
            $this->user,
            $project,
            $this->semester
        );

        expect($enrollment)->toBeInstanceOf(UserSelection::class);
    });
});

describe('registerPriorityChoices', function (): void {
    it('creates ordered selections for AWPF electives', function (): void {
        Event::fake();

        $awpf1 = Awpf::factory()->create();
        $awpf2 = Awpf::factory()->create();
        $awpf3 = Awpf::factory()->create();

        $selections = $this->service->registerPriorityChoices(
            $this->user,
            $this->semester,
            Awpf::class,
            [$awpf1->id, $awpf2->id, $awpf3->id]
        );

        expect($selections)->toHaveCount(3);

        // First choice has no parent
        expect($selections[0])
            ->elective_choice_id->toBe($awpf1->id)
            ->parent_elective_choice_id->toBeNull()
            ->enrollment_type->toBe(EnrollmentType::Priority);

        // Second choice has first as parent
        expect($selections[1])
            ->elective_choice_id->toBe($awpf2->id)
            ->parent_elective_choice_id->toBe($selections[0]->id);

        // Third choice has second as parent
        expect($selections[2])
            ->elective_choice_id->toBe($awpf3->id)
            ->parent_elective_choice_id->toBe($selections[1]->id);

        Event::assertDispatched(PriorityChoicesRegistered::class, fn (PriorityChoicesRegistered $event): bool => $event->user->id === $this->user->id
            && $event->semester->id === $this->semester->id
            && $event->electiveType === Awpf::class
            && $event->selections->count() === 3);
    });

    it('creates ordered selections for FWPM electives', function (): void {
        $fwpm1 = Fwpm::factory()->create();
        $fwpm2 = Fwpm::factory()->create();

        $selections = $this->service->registerPriorityChoices(
            $this->user,
            $this->semester,
            Fwpm::class,
            [$fwpm1->id, $fwpm2->id]
        );

        expect($selections)->toHaveCount(2)
            ->and($selections[0]->elective_type)->toBe(Fwpm::class)
            ->and($selections[1]->elective_type)->toBe(Fwpm::class);
    });

    it('replaces existing selections when registering new choices', function (): void {
        $awpf1 = Awpf::factory()->create();
        $awpf2 = Awpf::factory()->create();
        $awpf3 = Awpf::factory()->create();

        // First registration
        $this->service->registerPriorityChoices(
            $this->user,
            $this->semester,
            Awpf::class,
            [$awpf1->id, $awpf2->id]
        );

        // Second registration with different choices
        $this->service->registerPriorityChoices(
            $this->user,
            $this->semester,
            Awpf::class,
            [$awpf3->id]
        );

        $allSelections = UserSelection::query()
            ->forUser($this->user)
            ->forSemester($this->semester)
            ->where('elective_type', Awpf::class)
            ->get();

        expect($allSelections)->toHaveCount(1)
            ->and($allSelections->first()->elective_choice_id)->toBe($awpf3->id);
    });

    it('returns empty collection when no electives provided', function (): void {
        $selections = $this->service->registerPriorityChoices(
            $this->user,
            $this->semester,
            Awpf::class,
            []
        );

        expect($selections)->toHaveCount(0);
    });
});

describe('withdraw', function (): void {
    it('withdraws pending enrollment', function (): void {
        Event::fake();

        $selection = UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => ResearchProject::class,
            'elective_choice_id' => ResearchProject::factory()->create()->id,
            'status' => EnrollmentStatus::Pending,
            'enrollment_type' => EnrollmentType::Direct,
        ]);

        $this->service->withdraw($selection);

        expect($selection->fresh()->status)->toBe(EnrollmentStatus::Withdrawn);

        Event::assertDispatched(EnrollmentWithdrawn::class);
    });

    it('withdraws rejected enrollment', function (): void {
        $selection = UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => ResearchProject::class,
            'elective_choice_id' => ResearchProject::factory()->create()->id,
            'status' => EnrollmentStatus::Rejected,
            'enrollment_type' => EnrollmentType::Direct,
        ]);

        $this->service->withdraw($selection);

        expect($selection->fresh()->status)->toBe(EnrollmentStatus::Withdrawn);
    });

    it('throws exception when withdrawing confirmed enrollment', function (): void {
        $selection = UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => ResearchProject::class,
            'elective_choice_id' => ResearchProject::factory()->create()->id,
            'status' => EnrollmentStatus::Confirmed,
            'enrollment_type' => EnrollmentType::Direct,
        ]);

        expect(fn () => $this->service->withdraw($selection))
            ->toThrow(InvalidEnrollmentStatusException::class);
    });
});

describe('confirm', function (): void {
    it('confirms pending enrollment', function (): void {
        Event::fake();

        $selection = UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => Awpf::class,
            'elective_choice_id' => Awpf::factory()->create()->id,
            'status' => EnrollmentStatus::Pending,
            'enrollment_type' => EnrollmentType::Priority,
        ]);

        $this->service->confirm($selection);

        expect($selection->fresh()->status)->toBe(EnrollmentStatus::Confirmed);

        Event::assertDispatched(EnrollmentConfirmed::class);
    });

    it('confirms research project enrollment when capacity available', function (): void {
        $project = ResearchProject::factory()->create(['max_students' => 5]);

        $selection = UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => ResearchProject::class,
            'elective_choice_id' => $project->id,
            'status' => EnrollmentStatus::Pending,
            'enrollment_type' => EnrollmentType::Direct,
        ]);

        $this->service->confirm($selection);

        expect($selection->fresh()->status)->toBe(EnrollmentStatus::Confirmed);
    });

    it('throws exception when confirming non-pending enrollment', function (): void {
        $selection = UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => Awpf::class,
            'elective_choice_id' => Awpf::factory()->create()->id,
            'status' => EnrollmentStatus::Confirmed,
            'enrollment_type' => EnrollmentType::Priority,
        ]);

        expect(fn () => $this->service->confirm($selection))
            ->toThrow(InvalidEnrollmentStatusException::class);
    });

    it('throws exception when research project is at capacity during confirmation', function (): void {
        $project = ResearchProject::factory()->create(['max_students' => 1]);

        // Another user already confirmed
        UserSelection::query()->create([
            'user_id' => User::factory()->create()->id,
            'semester_id' => $this->semester->id,
            'elective_type' => ResearchProject::class,
            'elective_choice_id' => $project->id,
            'status' => EnrollmentStatus::Confirmed,
            'enrollment_type' => EnrollmentType::Direct,
        ]);

        $selection = UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => ResearchProject::class,
            'elective_choice_id' => $project->id,
            'status' => EnrollmentStatus::Pending,
            'enrollment_type' => EnrollmentType::Direct,
        ]);

        expect(fn () => $this->service->confirm($selection))
            ->toThrow(CapacityExceededException::class);
    });
});

describe('reject', function (): void {
    it('rejects pending enrollment', function (): void {
        Event::fake();

        $selection = UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => Awpf::class,
            'elective_choice_id' => Awpf::factory()->create()->id,
            'status' => EnrollmentStatus::Pending,
            'enrollment_type' => EnrollmentType::Priority,
        ]);

        $this->service->reject($selection);

        expect($selection->fresh()->status)->toBe(EnrollmentStatus::Rejected);

        Event::assertDispatched(EnrollmentRejected::class);
    });

    it('throws exception when rejecting non-pending enrollment', function (): void {
        $selection = UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => Awpf::class,
            'elective_choice_id' => Awpf::factory()->create()->id,
            'status' => EnrollmentStatus::Confirmed,
            'enrollment_type' => EnrollmentType::Priority,
        ]);

        expect(fn () => $this->service->reject($selection))
            ->toThrow(InvalidEnrollmentStatusException::class);
    });
});

describe('getUserEnrollments', function (): void {
    it('returns all enrollments for user in semester', function (): void {
        $awpf = Awpf::factory()->create();
        $project = ResearchProject::factory()->create();

        UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => Awpf::class,
            'elective_choice_id' => $awpf->id,
            'status' => EnrollmentStatus::Pending,
            'enrollment_type' => EnrollmentType::Priority,
        ]);

        UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => ResearchProject::class,
            'elective_choice_id' => $project->id,
            'status' => EnrollmentStatus::Confirmed,
            'enrollment_type' => EnrollmentType::Direct,
        ]);

        $enrollments = $this->service->getUserEnrollments($this->user, $this->semester);

        expect($enrollments)->toHaveCount(2);
    });

    it('does not return enrollments from other semesters', function (): void {
        $otherSemester = Semester::factory()->year(2025)->summer()->create();
        $awpf = Awpf::factory()->create();

        UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $otherSemester->id,
            'elective_type' => Awpf::class,
            'elective_choice_id' => $awpf->id,
            'status' => EnrollmentStatus::Pending,
            'enrollment_type' => EnrollmentType::Priority,
        ]);

        $enrollments = $this->service->getUserEnrollments($this->user, $this->semester);

        expect($enrollments)->toHaveCount(0);
    });
});

describe('getConfirmedEnrollments', function (): void {
    it('returns only confirmed enrollments', function (): void {
        $awpf1 = Awpf::factory()->create();
        $awpf2 = Awpf::factory()->create();

        UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => Awpf::class,
            'elective_choice_id' => $awpf1->id,
            'status' => EnrollmentStatus::Confirmed,
            'enrollment_type' => EnrollmentType::Priority,
        ]);

        UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => Awpf::class,
            'elective_choice_id' => $awpf2->id,
            'status' => EnrollmentStatus::Pending,
            'enrollment_type' => EnrollmentType::Priority,
        ]);

        $confirmed = $this->service->getConfirmedEnrollments($this->user, $this->semester);

        expect($confirmed)->toHaveCount(1)
            ->and($confirmed->first()->elective_choice_id)->toBe($awpf1->id);
    });

    it('returns confirmed enrollments across all semesters when semester is null', function (): void {
        $otherSemester = Semester::factory()->year(2025)->summer()->create();
        $awpf1 = Awpf::factory()->create();
        $awpf2 = Awpf::factory()->create();

        UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => Awpf::class,
            'elective_choice_id' => $awpf1->id,
            'status' => EnrollmentStatus::Confirmed,
            'enrollment_type' => EnrollmentType::Priority,
        ]);

        UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $otherSemester->id,
            'elective_type' => Awpf::class,
            'elective_choice_id' => $awpf2->id,
            'status' => EnrollmentStatus::Confirmed,
            'enrollment_type' => EnrollmentType::Priority,
        ]);

        $confirmed = $this->service->getConfirmedEnrollments($this->user);

        expect($confirmed)->toHaveCount(2);
    });
});

describe('hasActiveResearchProjectEnrollment', function (): void {
    it('returns true when user has pending research project enrollment', function (): void {
        $project = ResearchProject::factory()->create();

        UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => ResearchProject::class,
            'elective_choice_id' => $project->id,
            'status' => EnrollmentStatus::Pending,
            'enrollment_type' => EnrollmentType::Direct,
        ]);

        expect($this->service->hasActiveResearchProjectEnrollment($this->user, $this->semester))
            ->toBeTrue();
    });

    it('returns true when user has confirmed research project enrollment', function (): void {
        $project = ResearchProject::factory()->create();

        UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => ResearchProject::class,
            'elective_choice_id' => $project->id,
            'status' => EnrollmentStatus::Confirmed,
            'enrollment_type' => EnrollmentType::Direct,
        ]);

        expect($this->service->hasActiveResearchProjectEnrollment($this->user, $this->semester))
            ->toBeTrue();
    });

    it('returns false when user has withdrawn research project enrollment', function (): void {
        $project = ResearchProject::factory()->create();

        UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => ResearchProject::class,
            'elective_choice_id' => $project->id,
            'status' => EnrollmentStatus::Withdrawn,
            'enrollment_type' => EnrollmentType::Direct,
        ]);

        expect($this->service->hasActiveResearchProjectEnrollment($this->user, $this->semester))
            ->toBeFalse();
    });

    it('returns false when user has no research project enrollment', function (): void {
        expect($this->service->hasActiveResearchProjectEnrollment($this->user, $this->semester))
            ->toBeFalse();
    });

    it('returns false when user only has AWPF enrollment', function (): void {
        $awpf = Awpf::factory()->create();

        UserSelection::query()->create([
            'user_id' => $this->user->id,
            'semester_id' => $this->semester->id,
            'elective_type' => Awpf::class,
            'elective_choice_id' => $awpf->id,
            'status' => EnrollmentStatus::Pending,
            'enrollment_type' => EnrollmentType::Priority,
        ]);

        expect($this->service->hasActiveResearchProjectEnrollment($this->user, $this->semester))
            ->toBeFalse();
    });
});

describe('exception error messages', function (): void {
    it('CapacityExceededException has correct error structure', function (): void {
        $exception = CapacityExceededException::forResearchProject();

        expect($exception->getMessage())->toBe('This research project has reached maximum capacity.')
            ->and($exception->getErrors())->toBe([
                'project' => ['This research project has reached maximum capacity.'],
            ]);
    });

    it('DuplicateEnrollmentException has correct error structure', function (): void {
        $exception = DuplicateEnrollmentException::forResearchProject();

        expect($exception->getMessage())->toBe('You are already enrolled in this project.')
            ->and($exception->getErrors())->toBe([
                'project' => ['You are already enrolled in this project.'],
            ]);
    });

    it('InvalidEnrollmentStatusException has correct error structure', function (): void {
        $exception = InvalidEnrollmentStatusException::cannotWithdrawConfirmed();

        expect($exception->getMessage())->toBe('Cannot withdraw from confirmed enrollment. Please contact administrator.')
            ->and($exception->getErrors())->toBe([
                'selection' => ['Cannot withdraw from confirmed enrollment. Please contact administrator.'],
            ]);
    });
});
