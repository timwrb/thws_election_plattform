<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Enums\EnrollmentType;
use App\Models\ResearchProject;
use App\Models\Semester;
use App\Models\User;
use App\Models\UserSelection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentService
{
    /**
     * Enroll user in research project
     */
    public function enrollInResearchProject(
        User $user,
        ResearchProject $project,
        Semester $semester
    ): UserSelection {
        return DB::transaction(function () use ($user, $project, $semester) {
            // Validate capacity
            if (! $project->hasCapacity($semester)) {
                throw ValidationException::withMessages([
                    'project' => 'This research project has reached maximum capacity.',
                ]);
            }

            // Check for duplicate enrollment
            $existing = UserSelection::query()
                ->forUser($user)
                ->forSemester($semester)
                ->where('elective_type', ResearchProject::class)
                ->where('elective_choice_id', $project->id)
                ->whereIn('status', [EnrollmentStatus::Pending, EnrollmentStatus::Confirmed])
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'project' => 'You are already enrolled in this project.',
                ]);
            }

            // Create enrollment with pending status (requires admin approval)
            return UserSelection::query()->create([
                'user_id' => $user->id,
                'semester_id' => $semester->id,
                'elective_type' => ResearchProject::class,
                'elective_choice_id' => $project->id,
                'parent_elective_choice_id' => null,
                'status' => EnrollmentStatus::Pending,
                'enrollment_type' => EnrollmentType::Direct,
            ]);
        });
    }

    /**
     * Register ordered choices for AWPF or FWPM
     */
    public function registerPriorityChoices(
        User $user,
        Semester $semester,
        string $electiveType,
        array $orderedElectiveIds
    ): void {
        DB::transaction(function () use ($user, $semester, $electiveType, $orderedElectiveIds): void {
            // Delete existing choices for this semester and type
            UserSelection::query()
                ->forUser($user)
                ->forSemester($semester)
                ->where('elective_type', $electiveType)
                ->delete();

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

                $parentId = $selection->id;
            }
        });
    }

    /**
     * Withdraw from enrollment
     */
    public function withdraw(UserSelection $selection): void
    {
        if ($selection->status === EnrollmentStatus::Confirmed) {
            throw ValidationException::withMessages([
                'selection' => 'Cannot withdraw from confirmed enrollment. Please contact administrator.',
            ]);
        }

        $selection->update(['status' => EnrollmentStatus::Withdrawn]);
    }

    /**
     * Confirm enrollment (admin action)
     */
    public function confirm(UserSelection $selection): void
    {
        if ($selection->status !== EnrollmentStatus::Pending) {
            throw ValidationException::withMessages([
                'selection' => 'Can only confirm pending enrollments.',
            ]);
        }

        // Check capacity for research projects
        if ($selection->elective_type === ResearchProject::class) {
            $project = $selection->elective;
            if (! $project->hasCapacity($selection->semester)) {
                throw ValidationException::withMessages([
                    'selection' => 'Research project has reached maximum capacity.',
                ]);
            }
        }

        $selection->update(['status' => EnrollmentStatus::Confirmed]);
    }

    /**
     * Reject enrollment (admin action)
     */
    public function reject(UserSelection $selection): void
    {
        if ($selection->status !== EnrollmentStatus::Pending) {
            throw ValidationException::withMessages([
                'selection' => 'Can only reject pending enrollments.',
            ]);
        }

        $selection->update(['status' => EnrollmentStatus::Rejected]);
    }
}
