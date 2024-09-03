<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;
    protected $fillable = [
        'assessmentID',
        'Lesson_ID',
        'Title',
        'Instruction',
        'Description',
        'Due_date'
    ];
}
