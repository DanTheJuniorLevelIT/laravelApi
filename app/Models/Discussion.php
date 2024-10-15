<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discussion extends Model
{
    use HasFactory;
    protected $fillable = [
        'discussionid',
        'lesson_id',
        'discussion_topic',
        'created_at'
    ];
}
