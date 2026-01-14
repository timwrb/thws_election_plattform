<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSemester extends Model
{
    protected $table = 'user_semester';

    protected $fillable = [
        'user_id',
        'semester_id',
        'semester_number',
        'is_current',
    ];

    protected function casts(): array
    {
        return [
            'semester_number' => 'integer',
            'is_current' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Semester, $this>
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * @param  Builder<UserSemester>  $query
     * @return Builder<UserSemester>
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    /**
     * @param  Builder<UserSemester>  $query
     * @return Builder<UserSemester>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * @param  Builder<UserSemester>  $query
     * @return Builder<UserSemester>
     */
    public function scopeForSemester(Builder $query, Semester $semester): Builder
    {
        return $query->where('semester_id', $semester->id);
    }
}
