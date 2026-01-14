<?php

namespace App\Models;

use App\Traits\HasOrderedUserChoices;
use Database\Factories\ResearchProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $supervisor
 * @property int|null $creator_id
 * @property int|null $semester_id
 * @property int $credits
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property int $max_students
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ResearchProject extends Model
{
    /** @use HasFactory<ResearchProjectFactory> */
    use HasFactory;

    use HasOrderedUserChoices;

    protected $fillable = [
        'title',
        'description',
        'supervisor',
        'creator_id',
        'semester_id',
        'credits',
        'start_date',
        'end_date',
        'max_students',
    ];

    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'max_students' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * @return BelongsTo<Semester, $this>
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * @return HasMany<UserSelection, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(UserSelection::class, 'elective_choice_id')
            ->where('elective_type', self::class);
    }

    /**
     * @param  Builder<ResearchProject>  $query
     * @return Builder<ResearchProject>
     */
    public function scopeForSemester(Builder $query, Semester $semester): Builder
    {
        return $query->where('semester_id', $semester->id);
    }

    /**
     * @param  Builder<ResearchProject>  $query
     * @return Builder<ResearchProject>
     */
    public function scopeCreatedByStudent(Builder $query): Builder
    {
        return $query->whereNotNull('creator_id');
    }

    /**
     * @param  Builder<ResearchProject>  $query
     * @return Builder<ResearchProject>
     */
    public function scopeCreatedByAdmin(Builder $query): Builder
    {
        return $query->whereNull('creator_id');
    }

    /**
     * @param  Builder<ResearchProject>  $query
     * @return Builder<ResearchProject>
     */
    public function scopeWithAvailableCapacity(Builder $query, Semester $semester): Builder
    {
        return $query->whereHas('enrollments', function (Builder $q) use ($semester): void {
            $q->where('semester_id', $semester->id)
                ->whereIn('status', ['pending', 'confirmed']);
        }, '<', DB::raw('max_students'));
    }

    public function getCurrentEnrollmentCount(Semester $semester): int
    {
        return $this->enrollments()
            ->forSemester($semester)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();
    }

    public function getAvailableSpots(Semester $semester): int
    {
        return max(0, $this->max_students - $this->getCurrentEnrollmentCount($semester));
    }

    public function hasCapacity(Semester $semester): bool
    {
        return $this->getAvailableSpots($semester) > 0;
    }

    public function isFull(Semester $semester): bool
    {
        return ! $this->hasCapacity($semester);
    }

    public function isUserEnrolled(User $user, Semester $semester): bool
    {
        return $this->enrollments()
            ->forUser($user)
            ->forSemester($semester)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();
    }

    public function isCreatedBy(User $user): bool
    {
        return $this->creator_id === $user->id;
    }
}
