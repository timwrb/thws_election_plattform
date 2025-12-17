<?php

namespace App\Models;

use App\Traits\HasOrderedUserChoices;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fwpm extends Model
{
    /** @use HasFactory<\Database\Factories\FwpmFactory> */
    use HasFactory;
    use HasOrderedUserChoices;
}
