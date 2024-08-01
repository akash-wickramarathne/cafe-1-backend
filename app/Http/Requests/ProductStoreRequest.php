<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize()
    {

        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|gt:0',
            'stock' => 'required|numeric|gte:0',
            'category' => 'required|integer|exists:food_categories,food_category_id',
            'imagePaths' => 'required|array',
            'imagePaths.*' => 'required|string'
        ];
    }


    public function messages()
    {
        return [
            'name.required' => 'The product name is required.',
            'name.string' => 'The product name must be a string.',
            'name.max' => 'The product name may not be greater than 255 characters.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'price.required' => 'The price is required.',
            'price.numeric' => 'The price must be a number.',
            'price.gt' => 'The price must be greater than zero.',
            'stock.required' => 'The stock is required.',
            'stock.numeric' => 'The stock must be a number.',
            'stock.gte' => 'The stock must be greater than or equal to zero.',
            'category.required' => 'The category is required.',
            'category.integer' => 'The category must be an integer.',
            'category.exists' => 'The selected category is invalid.',
            'imagePaths.required' => 'At least one image path is required.',
            'imagePaths.array' => 'The image paths must be an array.',
            'imagePaths.*.required' => 'Each image path is required.',
            'imagePaths.*.string' => 'Each image path must be a string.',
            'imagePaths.*.url' => 'Each image path must be a valid URL.',
        ];
    }
}
