<?php

namespace App\Models;

use App\Models\Items\FoodItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'food_item_id',
        'order_id',
        'quantity',
        'price'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function foodItem()
    {
        return $this->belongsTo(FoodItem::class, 'food_item_id');
    }
}
