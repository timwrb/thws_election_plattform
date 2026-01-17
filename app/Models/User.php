<?php

namespace App\Models;

use App\Services\SemesterService;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string $id
 * @property string|null $salutation
 * @property string $name
 * @property string $surname
 * @property string $email
 * @property int|null $start_semester_id
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class User extends Authenticatable implements FilamentUser, HasAvatar, HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    use HasRoles;
    use HasUuids;
    use InteractsWithMedia;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return "$this->salutation $this->name $this->surname";
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        if ($panel->getId() === 'electives') {
            return $this->hasRole('student');
        }

        return $panel->isDefault();
    }

    /**
     * @return HasMany<UserSelection, $this>
     */
    public function selections(): HasMany
    {
        return $this->hasMany(UserSelection::class);
    }

    /**
     * @return Collection<int, UserSelection>
     */
    public function selectionsForSemester(Semester $semester): Collection
    {
        return $this->selections()
            ->forSemester($semester)
            ->with('elective')
            ->get();
    }

    /**
     * @return MorphToMany<Awpf, $this>
     */
    public function awpfSelections(): MorphToMany
    {
        return $this->morphedByMany(
            Awpf::class,
            'elective',
            'user_selections',
            'user_id',
            'elective_choice_id'
        )
            ->withPivot(['semester_id', 'parent_elective_choice_id', 'status', 'enrollment_type', 'id'])
            ->withTimestamps();
    }

    /**
     * @return MorphToMany<Fwpm, $this>
     */
    public function fwpmSelections(): MorphToMany
    {
        return $this->morphedByMany(
            Fwpm::class,
            'elective',
            'user_selections',
            'user_id',
            'elective_choice_id'
        )
            ->withPivot(['semester_id', 'parent_elective_choice_id', 'status', 'enrollment_type', 'id'])
            ->withTimestamps();
    }

    /**
     * @return MorphToMany<ResearchProject, $this>
     */
    public function researchProjectSelections(): MorphToMany
    {
        return $this->morphedByMany(
            ResearchProject::class,
            'elective',
            'user_selections',
            'user_id',
            'elective_choice_id'
        )
            ->withPivot(['semester_id', 'status', 'enrollment_type', 'id'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<ResearchProject, $this>
     */
    public function createdResearchProjects(): HasMany
    {
        return $this->hasMany(ResearchProject::class, 'creator_id');
    }

    /**
     * @return BelongsTo<Semester, $this>
     */
    public function startSemester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'start_semester_id');
    }

    /**
     * Get the current active semester based on today's date.
     */
    public function getCurrentSemester(): ?Semester
    {
        return app(SemesterService::class)->getCurrentSemester();
    }

    /**
     * Calculate which semester number this user is in based on their start semester.
     *
     * Example: If user started in WS23/24 and current semester is WS25/26, returns 5.
     */
    public function getSemesterNumber(): ?int
    {
        return app(SemesterService::class)->calculateSemesterNumber($this);
    }

    public function canEnrollInResearchProject(ResearchProject $project, Semester $semester): bool
    {
        $existingEnrollment = $this->researchProjectSelections()
            ->wherePivot('semester_id', $semester->id)
            ->wherePivot('elective_choice_id', $project->id)
            ->wherePivotIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($existingEnrollment) {
            return false;
        }

        return $project->hasCapacity($semester);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->getMedia('avatars')->first()?->getUrl();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatars')
            ->useDisk('public')
            ->singleFile();
    }
}
