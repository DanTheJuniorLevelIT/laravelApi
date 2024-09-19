<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discussion_Reply extends Model
{
    use HasFactory;

    protected $table = 'discussion_replies';
    protected $fillable = [
        'replyid',
        'discussionid',
        'lrn',
        'adminID',
        'reply'
    ];
}
