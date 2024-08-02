<?php

namespace App\Models\categories;

use App\Models\Items\FoodItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodCategory extends Model
{
    use HasFactory;

    protected $primaryKey = "food_category_id";

    protected $fillable = ['food_type_name', 'food_type_description', 'create_admin_id'];
    public function foodItems()
    {
        // Ensure the foreign key and local key are correctly specified
        return $this->hasMany(FoodItem::class, 'food_category_id', 'food_category_id');
    }
}
