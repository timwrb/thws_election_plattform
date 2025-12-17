<?php

namespace App\Models;

use App\Traits\HasOrderedUserChoices;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Awpf extends Model
{
    /** @use HasFactory<\Database\Factories\AwpfFactory> */
    use HasFactory;
    use HasOrderedUserChoices;
}
