<?php

namespace App\Models;

use App\Enums\DayOfWeek;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CourseSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\CourseScheduleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'day_of_week' => DayOfWeek::class,
            'start_time' => 'datetime:H:i',
            'duration_minutes' => 'integer',
        ];
    }

    public function schedulable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getFormattedScheduleAttribute(): string
    {
        return sprintf(
            'Termin: %s %s (%d min.)',
            $this->day_of_week->getAbbreviation(),
            $this->start_time->format('H:i'),
            $this->duration_minutes
        );
    }

    public function scopeForDay(Builder $query, DayOfWeek $day): Builder
    {
        return $query->where('day_of_week', $day);
    }

    public function scopeOrderedByDay(Builder $query): Builder
    {
        return $query->orderByRaw("
            FIELD(day_of_week, 'monday', 'tuesday', 'wednesday',
                  'thursday', 'friday', 'saturday', 'sunday')
        ")->orderBy('start_time');
    }
}
