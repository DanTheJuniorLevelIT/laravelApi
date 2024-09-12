<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;
    protected $fillable = [
        'lesson_id',
        'module_id',
        'topic_title',
        'lesson',
        'handout',
        'file'
    ];
}
