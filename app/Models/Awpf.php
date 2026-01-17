<?php

namespace App\Models;

use App\Enums\ExamType;
use App\Enums\Language;
use App\Traits\HasOrderedUserChoices;
use App\Traits\HasSemester;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $content
 * @property int $credits
 * @property Language $language
 * @property ExamType $exam_type
 * @property int|null $professor_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $formatted_schedules
 */
class Awpf extends Model
{
    /** @use HasFactory<\Database\Factories\AwpfFactory> */
    use HasFactory;

    use HasOrderedUserChoices;
    use HasSemester;

    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'language' => Language::class,
            'exam_type' => ExamType::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professor_id');
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
