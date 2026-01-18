<?php

namespace App\Filament\Electives\Widgets;

use App\Enums\EnrollmentStatus;
use App\Models\Awpf;
use App\Models\Fwpm;
use App\Models\ResearchProject;
use App\Models\Semester;
use App\Models\User;
use App\Models\UserSelection;
use App\Services\SemesterService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class EnrollmentOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    #[\Override]
    protected function getStats(): array
    {
        $semester = resolve(SemesterService::class)->getCurrentSemester();

        if (! $semester instanceof Semester) {
            return [
                Stat::make('No Active Semester', 'Please contact administration')
                    ->description('No semester is currently configured')
                    ->color('warning'),
            ];
        }
        if (! Auth::check()) {
            return [
                Stat::make('There was an error loading the data', 'Please contact administration')
                    ->description('No semester is currently configured')
                    ->color('warning'),
            ];
        }

        /* @var User $user */
        $user = Auth::user();

        $awpfCount = UserSelection::query()
            ->forUser($user)
            ->forSemester($semester)
            ->where('elective_type', Awpf::class)
            ->whereIn('status', [EnrollmentStatus::Pending, EnrollmentStatus::Confirmed])
            ->count();

        $maxAwpf = config('electives.max_selections.awpf', 2);

        $fwpmCount = UserSelection::query()
            ->forUser($user)
            ->forSemester($semester)
            ->where('elective_type', Fwpm::class)
            ->whereIn('status', [EnrollmentStatus::Pending, EnrollmentStatus::Confirmed])
            ->count();

        $maxFwpm = config('electives.max_selections.fwpm', 3);
        $researchCount = UserSelection::query()
            ->forUser($user)
            ->forSemester($semester)
            ->where('elective_type', ResearchProject::class)
            ->whereIn('status', [EnrollmentStatus::Pending, EnrollmentStatus::Confirmed])
            ->count();

        $confirmedCount = UserSelection::query()
            ->forUser($user)
            ->forSemester($semester)
            ->where('status', EnrollmentStatus::Confirmed)
            ->count();

        return [
            Stat::make('AWPF Courses', "{$awpfCount} / {$maxAwpf}")
                ->description('General elective selections')
                ->descriptionIcon('heroicon-o-academic-cap')
                ->color($awpfCount > 0 ? 'success' : 'gray'),

            Stat::make('FWPM Courses', "{$fwpmCount} / {$maxFwpm}")
                ->description('Subject-specific selections')
                ->descriptionIcon('heroicon-o-book-open')
                ->color($fwpmCount > 0 ? 'success' : 'gray'),

            Stat::make('Research Projects', $researchCount)
                ->description('Project registrations')
                ->descriptionIcon('heroicon-o-beaker')
                ->color($researchCount > 0 ? 'success' : 'gray'),

            Stat::make('Confirmed Enrollments', $confirmedCount)
                ->description('Approved by administration')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color($confirmedCount > 0 ? 'success' : 'warning'),
        ];
    }
}
