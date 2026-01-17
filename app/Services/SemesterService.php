<?php

namespace App\Services;

use App\Enums\Season;
use App\Models\Semester;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SemesterService
{
    /**
     * Get the current active semester based on today's date.
     */
    public function getCurrentSemester(): ?Semester
    {
        $now = Carbon::now();
        $month = $now->month;
        $year = $now->year;

        if ($month >= 10) {
            $season = Season::Winter;
            $semesterYear = $year;
        } elseif ($month <= 3) {
            $season = Season::Winter;
            $semesterYear = $year - 1;
        } else {
            $season = Season::Summer;
            $semesterYear = $year;
        }

        return Semester::query()
            ->where('year', $semesterYear)
            ->where('season', $season)
            ->first();
    }

    /**
     * Calculate which semester number a user is in based on their start semester.
     */
    public function calculateSemesterNumber(User $user): ?int
    {
        if (! $user->startSemester instanceof Semester) {
            return null;
        }

        $currentSemester = $this->getCurrentSemester();

        if (! $currentSemester instanceof Semester) {
            return null;
        }

        return $this->getSemestersBetween($user->startSemester, $currentSemester) + 1;
    }

    /**
     * Calculate the number of semesters between two semesters.
     *
     * Returns the number of semester steps from start to end.
     */
    public function getSemestersBetween(Semester $start, Semester $end): int
    {
        $startIndex = $start->year * 2 + ($start->season === Season::Winter ? 1 : 0);
        $endIndex = $end->year * 2 + ($end->season === Season::Winter ? 1 : 0);

        return $endIndex - $startIndex;
    }

    /**
     * Get all semesters in chronological order.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Semester>
     */
    public function getAllSemestersOrdered(): Collection
    {
        return Semester::query()
            ->orderBy('year')
            ->orderByRaw("CASE WHEN season = 'SS' THEN 1 ELSE 2 END")
            ->get();
    }

    public function isPastSemester(Semester $semester): bool
    {
        $current = $this->getCurrentSemester();

        if (! $current instanceof Semester) {
            return false;
        }

        return $this->getSemestersBetween($semester, $current) > 0;
    }

    public function isFutureSemester(Semester $semester): bool
    {
        $current = $this->getCurrentSemester();

        if (! $current instanceof Semester) {
            return false;
        }

        return $this->getSemestersBetween($current, $semester) > 0;
    }

    public function isCurrentSemester(Semester $semester): bool
    {
        $current = $this->getCurrentSemester();

        if (! $current instanceof Semester) {
            return false;
        }

        return $semester->id === $current->id;
    }
}
