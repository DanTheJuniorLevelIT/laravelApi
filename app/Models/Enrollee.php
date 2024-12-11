<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollee extends Model
{
    use HasFactory;
    protected $fillable = [
        'lrn',
        'program',
        'status',
        'school_year',
        'enrolldate'
    ];
}
