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
        'downloaded_at', 'printed_at', 'admission_batch_id', 'reg_payment_status',
        'card_payment_transaction_id', 'card_payment_year_id'
    ];

    protected $dates = ['created_at', 'updated_at', 'dob', 'downloaded_at', 'printed_at'];

}
