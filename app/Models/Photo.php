<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';
    protected $fillable = ['student_id', 'path'];

    protected $table = 'files';
}
