<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartGetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // You should set this to true if authorization is not required.
        // For example, if all users can update the cart.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Added min rule to ensure quantity is at least 1
            'user_id' => ['required', 'exists:users,id']
        ];
    }

    /**
     * Get the custom validation messages for the rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [

            'user_id.required' => 'The user ID is required.',
            'user_id.exists' => 'The selected user ID does not exist in the users table.',
        ];
    }
}
