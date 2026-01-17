<?php

namespace App\Services;

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
use App\Models\ResearchProject;
use App\Models\Semester;
use App\Models\User;
use App\Models\UserSelection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class EnrollmentService
{
    /**
     * Enroll user in a research project.
     *
     * @throws CapacityExceededException
     * @throws DuplicateEnrollmentException
     * @throws Throwable
     */
    public function enrollInResearchProject(
        User $user,
        ResearchProject $project,
        Semester $semester
    ): UserSelection {
        return DB::transaction(function () use ($user, $project, $semester): UserSelection {
            $this->validateResearchProjectCapacity($project, $semester);
            $this->validateNoDuplicateResearchProjectEnrollment($user, $project, $semester);

            $enrollment = UserSelection::query()->create([
                'user_id' => $user->id,
                'semester_id' => $semester->id,
                'elective_type' => ResearchProject::class,
                'elective_choice_id' => $project->id,
                'parent_elective_choice_id' => null,
                'status' => EnrollmentStatus::Pending,
                'enrollment_type' => EnrollmentType::Direct,
            ]);

            EnrollmentCreated::dispatch($enrollment);

            return $enrollment;
        });
    }

    /**
     * Register ordered priority choices for AWPF or FWPM electives.
     *
     * @param  array<int, int|string>  $orderedElectiveIds  Array of elective IDs in priority order (first = highest priority)
     * @return Collection<int, UserSelection> The created selections in priority order
     *
     * @throws Throwable
     */
    public function registerPriorityChoices(
        User $user,
        Semester $semester,
        string $electiveType,
        array $orderedElectiveIds
    ): Collection {
        return DB::transaction(function () use ($user, $semester, $electiveType, $orderedElectiveIds): Collection {
            $this->deleteExistingSelections($user, $semester, $electiveType);

            $selections = $this->createPrioritySelections(
                $user,
                $semester,
                $electiveType,
                $orderedElectiveIds
            );

            PriorityChoicesRegistered::dispatch($user, $semester, $electiveType, $selections);

            return $selections;
        });
    }

    /**
     * Withdraw from an enrollment.
     *
     * Only pending or rejected enrollments can be withdrawn. Confirmed enrollments
     * require administrator intervention.
     *
     * @throws InvalidEnrollmentStatusException
     */
    public function withdraw(UserSelection $selection): void
    {
        if ($selection->status === EnrollmentStatus::Confirmed) {
            throw InvalidEnrollmentStatusException::cannotWithdrawConfirmed();
        }

        $selection->update(['status' => EnrollmentStatus::Withdrawn]);

        EnrollmentWithdrawn::dispatch($selection);
    }

    /**
     * Confirm a pending enrollment (admin action).
     *
     * For research projects, this also validates that capacity is still available
     * at the time of confirmation.
     *
     * @throws InvalidEnrollmentStatusException
     * @throws CapacityExceededException
     */
    public function confirm(UserSelection $selection): void
    {
        if ($selection->status !== EnrollmentStatus::Pending) {
            throw InvalidEnrollmentStatusException::canOnlyConfirmPending();
        }

        if ($selection->elective_type === ResearchProject::class) {
            /** @var ResearchProject $project */
            $project = $selection->elective;
            $this->validateResearchProjectCapacity($project, $selection->semester);
        }

        $selection->update(['status' => EnrollmentStatus::Confirmed]);

        EnrollmentConfirmed::dispatch($selection);
    }

    /**
     * Reject a pending enrollment.
     *
     * @throws InvalidEnrollmentStatusException
     */
    public function reject(UserSelection $selection): void
    {
        if ($selection->status !== EnrollmentStatus::Pending) {
            throw InvalidEnrollmentStatusException::canOnlyRejectPending();
        }

        $selection->update(['status' => EnrollmentStatus::Rejected]);

        EnrollmentRejected::dispatch($selection);
    }

    /**
     * Get all enrollments for a user in a specific semester.
     *
     * @return Collection<int, UserSelection>
     */
    public function getUserEnrollments(User $user, Semester $semester): Collection
    {
        return UserSelection::query()
            ->forUser($user)
            ->forSemester($semester)
            ->withElective()
            ->get();
    }

    /**
     * Get all confirmed enrollments for a user.
     *
     * @return Collection<int, UserSelection>
     */
    public function getConfirmedEnrollments(User $user, ?Semester $semester = null): Collection
    {
        $query = UserSelection::query()
            ->forUser($user)
            ->confirmed()
            ->withElective();

        if ($semester instanceof \App\Models\Semester) {
            $query->forSemester($semester);
        }

        return $query->get();
    }

    /**
     * Check if user has any active enrollment in a research project for a semester.
     */
    public function hasActiveResearchProjectEnrollment(User $user, Semester $semester): bool
    {
        return UserSelection::query()
            ->forUser($user)
            ->forSemester($semester)
            ->researchProjects()
            ->whereIn('status', [EnrollmentStatus::Pending, EnrollmentStatus::Confirmed])
            ->exists();
    }

    /**
     * Validate that the research project has available capacity.
     *
     * @throws CapacityExceededException
     */
    private function validateResearchProjectCapacity(ResearchProject $project, Semester $semester): void
    {
        if (! $project->hasCapacity($semester)) {
            throw CapacityExceededException::forResearchProject();
        }
    }

    /**
     * Validate that the user doesn't already have an active enrollment in the project.
     *
     * @throws DuplicateEnrollmentException
     */
    private function validateNoDuplicateResearchProjectEnrollment(
        User $user,
        ResearchProject $project,
        Semester $semester
    ): void {
        $existing = UserSelection::query()
            ->forUser($user)
            ->forSemester($semester)
            ->where('elective_type', ResearchProject::class)
            ->where('elective_choice_id', $project->id)
            ->whereIn('status', [EnrollmentStatus::Pending, EnrollmentStatus::Confirmed])
            ->exists();

        if ($existing) {
            throw DuplicateEnrollmentException::forResearchProject();
        }
    }

    /**
     * Delete existing selections for a user/semester/type combination.
     */
    private function deleteExistingSelections(User $user, Semester $semester, string $electiveType): void
    {
        UserSelection::query()
            ->forUser($user)
            ->forSemester($semester)
            ->where('elective_type', $electiveType)
            ->delete();
    }

    /**
     * Create priority-ordered selections for electives.
     *
     * @param  array<int, int|string>  $orderedElectiveIds
     * @return Collection<int, UserSelection>
     */
    private function createPrioritySelections(
        User $user,
        Semester $semester,
        string $electiveType,
        array $orderedElectiveIds
    ): Collection {
        $selections = collect();
        $parentId = null;

        foreach ($orderedElectiveIds as $electiveId) {
            $selection = UserSelection::query()->create([
                'user_id' => $user->id,
                'semester_id' => $semester->id,
                'elective_type' => $electiveType,
                'elective_choice_id' => $electiveId,
                'parent_elective_choice_id' => $parentId,
                'status' => EnrollmentStatus::Pending,
                'enrollment_type' => EnrollmentType::Priority,
            ]);

            $selections->push($selection);
            $parentId = $selection->id;
        }

        return $selections;
    }
}
