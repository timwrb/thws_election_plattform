<?php

namespace App\Services;

use App\Enums\Season;
use App\Models\Semester;
use App\Models\User;
use Carbon\Carbon;

class SemesterService
{
    /**
     * Get the current active semester based on today's date.
     *
     * Winter Semester (WS): October 1 - March 31
     * Summer Semester (SS): April 1 - September 30
     */
    public function getCurrentSemester(): ?Semester
    {
        $now = Carbon::now();
        $month = $now->month;
        $year = $now->year;

        // Determine which semester we're in based on the month
        if ($month >= 10) {
            // October-December: Winter semester of current year
            $season = Season::Winter;
            $semesterYear = $year;
        } elseif ($month <= 3) {
            // January-March: Winter semester of previous year
            $season = Season::Winter;
            $semesterYear = $year - 1;
        } else {
            // April-September: Summer semester of current year
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
     *
     * Example: If user started in WS23/24 and current semester is WS25/26,
     * they are in their 5th semester.
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
     * Calculate the number of semesters between two semesters (inclusive).
     *
     * Example: WS23/24 to SS24 = 1 semester apart (2 total semesters)
     */
    public function getSemestersBetween(Semester $start, Semester $end): int
    {
        $count = 0;

        // Calculate year difference
        $yearDiff = $end->year - $start->year;

        if ($yearDiff === 0) {
            // Same year
            if ($start->season === Season::Winter && $end->season === Season::Summer) {
                $count = 1;
            } elseif ($start->season === Season::Summer && $end->season === Season::Winter) {
                $count = -1;
            }
            // Same season, same year = 0
        } else {
            // Different years
            // Each year has 2 semesters (Winter and Summer)
            $count = $yearDiff * 2;

            // Adjust based on seasons
            if ($start->season === Season::Summer) {
                $count -= 1;
            }

            if ($end->season === Season::Winter) {
                $count -= 1;
            }
        }

        return $count;
    }

    /**
     * Get all semesters in chronological order.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Semester>
     */
    public function getAllSemestersOrdered()
    {
        return Semester::query()
            ->orderBy('year', 'asc')
            ->orderByRaw("CASE WHEN season = 'WS' THEN 1 ELSE 2 END")
            ->get();
    }

    /**
     * Check if a semester is in the past relative to current semester.
     */
    public function isPastSemester(Semester $semester): bool
    {
        $current = $this->getCurrentSemester();

        if (! $current instanceof Semester) {
            return false;
        }

        return $this->getSemestersBetween($semester, $current) > 0;
    }

    /**
     * Check if a semester is in the future relative to current semester.
     */
    public function isFutureSemester(Semester $semester): bool
    {
        $current = $this->getCurrentSemester();

        if (! $current instanceof Semester) {
            return false;
        }

        return $this->getSemestersBetween($current, $semester) > 0;
    }

    /**
     * Check if a semester is the current active semester.
     */
    public function isCurrentSemester(Semester $semester): bool
    {
        $current = $this->getCurrentSemester();

        if (! $current instanceof Semester) {
            return false;
        }

        return $semester->id === $current->id;
    }
}
