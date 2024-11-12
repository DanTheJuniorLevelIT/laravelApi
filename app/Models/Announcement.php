<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;
    protected $primaryKey = 'subjectid';
    protected $fillable = [
        'subjectid',
        'title',
        'instruction'
    ];
}
