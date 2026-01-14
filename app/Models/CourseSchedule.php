<?php

namespace App\Models;

use App\Enums\DayOfWeek;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $schedulable_type
 * @property int $schedulable_id
 * @property DayOfWeek $day_of_week
 * @property \Illuminate\Support\Carbon $start_time
 * @property int $duration_minutes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $formatted_schedule
 */
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

    /**
     * @return MorphTo<Model, $this>
     */
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

    /**
     * @param  Builder<CourseSchedule>  $query
     * @return Builder<CourseSchedule>
     */
    public function scopeForDay(Builder $query, DayOfWeek $day): Builder
    {
        return $query->where('day_of_week', $day);
    }

    /**
     * @param  Builder<CourseSchedule>  $query
     * @return Builder<CourseSchedule>
     */
    public function scopeOrderedByDay(Builder $query): Builder
    {
        return $query->orderByRaw("
            FIELD(day_of_week, 'monday', 'tuesday', 'wednesday',
                  'thursday', 'friday', 'saturday', 'sunday')
        ")->orderBy('start_time');
    }
}
