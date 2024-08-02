<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'order_status_id',
        'stipe_session_id'
    ];

    protected $primaryKey = 'order_id';

    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    
}
