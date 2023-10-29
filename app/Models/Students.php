<?php

namespace App\Models;

use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Students extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'matric',
        'nationality',
        'level',
        'gender',
        'dob',
        'pob',
        'campus',
        'password',
        'school_id',
        'program',
        'img_url', 'created_at', 'updated_at'
    ];

    protected $dates = ['created_at', 'updated_at', 'dob'];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

}
