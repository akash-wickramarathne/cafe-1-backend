<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tables extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'size_id',
        'status_id',
        'seats'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
