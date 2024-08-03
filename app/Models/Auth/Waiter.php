<?php

namespace App\Models\Auth;

use App\Models\BookTable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waiter extends Model
{
    use HasFactory;

    protected $primaryKey = "waiter_id";
    protected $fillable = [
        'name',
        'email',
        'phone',
        'profile_image',
        'user_id'
    ];

    public $timestamps = false;

    public function bookTables()
    {

        return $this->hasMany(BookTable::class, 'waiter_id');
    }
}
