<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $fillable = [
        'question_id',
        'assessment_id',
        'question',
        'type',
        'key_answer',
        'points'
    ];

    public function options()
    {
        return $this->hasMany(Option::class);
    }
}
