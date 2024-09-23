<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;
    protected $table = 'answers';
    
    // Specify the correct primary key
    protected $primaryKey = 'answer_id';
    
    // Ensure that the primary key is not auto-incrementing if that's the case
    public $incrementing = true;

    // Define if the primary key is non-numeric
    protected $keyType = 'int';

    // Allow mass assignment on the 'score' field if you're using update() or create()
    // protected $fillable = ['score'];
    protected $fillable = [
        'answer_id',
        'question_id',
        'lrn',
        'answer',
        'score'
    ];
}
