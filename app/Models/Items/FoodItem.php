<?php

namespace App\Models\Items;

use App\Models\categories\FoodCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodItem extends Model
{
    use HasFactory;
    protected $primaryKey = 'food_item_id';
    // Fillable attributes for mass assignment
    protected $fillable = [
        'food_name',
        'description',
        'price',
        'stock',
        'food_images',
        'create_admin_id',
        'food_category_id', // Ensure this matches your database column
    ];

    protected $hidden = [
        'create_admin_id'
    ];

    // Define relationship with FoodCategory
    public function foodCategory()
    {
        // Ensure the foreign key and local key are correctly specified
        return $this->belongsTo(FoodCategory::class, 'food_category_id', 'food_category_id');
    }

    public function orderItems()
    {
        return $this->hasMany(FoodItem::class, 'food_item_id');
    }
}
