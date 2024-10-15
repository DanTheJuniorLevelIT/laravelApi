<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;
    protected $primaryKey = 'media_id';
    protected $fillable = [
        'media_id',
        'uploader_id',
        'lesson_id',
        'type',
        'filename'
    ];
}
