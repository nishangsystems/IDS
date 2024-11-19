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
        'name', 'matricule', 'dob', 'pob', 'sex', 'nationality', 'program', 'level',
        'photo', 'campus', 'status', 'date', 'img_path', 'link', 'user_id', 'valid',
        'downloaded_at', 'printed_at'
    ];

    protected $dates = ['created_at', 'updated_at', 'dob', 'downloaded_at', 'printed_at'];

}
