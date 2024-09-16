<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment_Answer extends Model
{
    use HasFactory;
    // Explicitly set the table name
    protected $table = 'assessment_answers';

    // Set the primary key column to 'answerid'
    protected $primaryKey = 'answerid';

    // If your primary key is not auto-incrementing, set this to false
    public $incrementing = false;

    // Set the type of the primary key if it's not an integer
    protected $keyType = 'string';
    protected $fillable = [
        'answerid',
        'lrn',
        'assessmentid',
        'link',
        'score',
        'date_submission',
        'file'
    ];
}
