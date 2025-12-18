<?php

namespace App\Models;

use App\Enums\ExamType;
use App\Enums\Language;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Awpf extends Model
{
    /** @use HasFactory<\Database\Factories\AwpfFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'language' => Language::class,
            'exam_type' => ExamType::class,
        ];
    }

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
