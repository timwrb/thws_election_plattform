<?php

namespace App\Models;

use App\Enums\ExamType;
use App\Enums\Language;
use App\Traits\HasOrderedUserChoices;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $content
 * @property int $credits
 * @property Language $language
 * @property ExamType $exam_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $formatted_schedules
 */
class Fwpm extends Model
{
    /** @use HasFactory<\Database\Factories\FwpmFactory> */
    use HasFactory;

    use HasOrderedUserChoices;

    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'language' => Language::class,
            'exam_type' => ExamType::class,
        ];
    }

    /**
     * @return MorphMany<CourseSchedule, $this>
     */
    public function schedules(): MorphMany
    {
        return $this->morphMany(CourseSchedule::class, 'schedulable');
    }

    public function getFormattedSchedulesAttribute(): string
    {
        return $this->schedules()
            ->orderedByDay()
            ->get()
            ->pluck('formatted_schedule')
            ->join(', ');
    }
}
