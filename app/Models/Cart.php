<?php

namespace App\Models;

use App\Models\Items\FoodItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'food_item_id',
        'user_id',
        'cart_qty',
    ];

    public function product()
    {
        return $this->belongsTo(FoodItem::class, 'food_item_id');
    }
}