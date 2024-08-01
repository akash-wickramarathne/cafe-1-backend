<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waiter extends Model
{
    use HasFactory;

    protected $primarykey = "waiter_id";
    protected $fillable = [
        'name',
        'email',
        'phone',
        'profile_image',
        'user_id'
    ];

    public $timestamps = false;
}
