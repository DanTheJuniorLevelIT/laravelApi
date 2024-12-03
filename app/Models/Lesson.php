<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;
    protected $primaryKey = 'lesson_id';
    protected $fillable = [
        'lesson_id',
        'module_id',
        'topic_title',
        'lesson',
        'handout',
        'file'
    ];
    
    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'lesson_id', 'lesson_id');
    }
}
