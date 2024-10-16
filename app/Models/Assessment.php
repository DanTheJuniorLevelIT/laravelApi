<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;
    protected $primaryKey = 'assessmentid';
    protected $fillable = [
        'assessmentid',
        'lesson_id',
        'title',
        'instruction',
        'description',
        'due_date'
    ];
}
