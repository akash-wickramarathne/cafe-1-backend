<?php

namespace App\Models;

use App\Models\Auth\Waiter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_time',
        'end_time',
        'waiter_id',
        'payment',
        'book_date',
        'table_status_id',
        'user_id',
        'stripe_session_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'table_status_id');
    }

    public function waiter(){
        return $this->belongsTo(Waiter::class,'waiter_id');
    }
}
