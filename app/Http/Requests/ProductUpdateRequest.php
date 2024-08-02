<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Update this as per your authorization logic
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'imagePaths' => 'required|array',
            'imagePaths.*' => 'string',
            'category' => 'required|exists:food_categories,food_category_id',
        ];
    }
}
