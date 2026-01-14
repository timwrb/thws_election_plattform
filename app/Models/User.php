<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $surname
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    use HasRoles;

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

    public function canAccessPanel(Panel $panel): bool
    {
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
}
