<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;
    protected $table = 'modules';
    protected $primaryKey = 'modules_id';
    protected $fillable = [
        'modules_id',
        'classid',
        'title',
        'description',
        'date'
    ];

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'module_id', 'modules_id');
    }
}
