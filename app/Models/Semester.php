<?php

namespace App\Models;

use App\Enums\Season;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'year',
        'season',
    ];

    protected function casts(): array
    {
        return [
            'season' => Season::class,
        ];
    }

    public function getLabel(): string
    {
        $yearShort = substr((string) $this->year, -2);

        if ($this->season === Season::Winter) {
            $nextYearShort = substr((string) ($this->year + 1), -2);

            return "{$this->season->value}{$yearShort}/{$nextYearShort}";
        }

        return "{$this->season->value}{$yearShort}";
    }
}
