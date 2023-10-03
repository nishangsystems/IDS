<?php

namespace App\Models;

use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class Students extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'matric',
        'phone',
        'address',
        'gender',
        'dob',
        'pob',
        'campus',
        'campus_id',
        'admission_batch_id',
        'password',
        'school_id',
        'program',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

}
