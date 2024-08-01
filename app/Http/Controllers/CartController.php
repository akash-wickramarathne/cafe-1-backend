<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddorUpdateCartRequest;
use App\Http\Requests\CartGetRequest;
use App\Models\Cart;
use App\Models\Items\FoodItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Store or update the cart item.
     *
     * @param  \App\Http\Requests\AddorUpdateCartRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(AddorUpdateCartRequest $request): JsonResponse
    {
        // Retrieve validated input data from the request
        $validated = $request->validated();

        // Begin a database transaction
        DB::beginTransaction();

        try {
            // Retrieve the food item to check its stock
            $foodItem = FoodItem::find($validated['product_id']);

            if (!$foodItem) {
                // Rollback and return an error if the food item is not found
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Food item not found.'], 404);
            }

            // Check if the stock is sufficient
            if ($foodItem->stock <= 0) {
                // Rollback and return an error if the stock is 0
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Product cannot be added to the cart due to insufficient stock.'], 400);
            }

            // Check if the cart item already exists
            $cartItem = Cart::where('food_item_id', $validated['product_id'])
                ->where('user_id', $validated['user_id'])
                ->first();

            if ($cartItem) {
                // Update existing cart item quantity
                $cartItem->cart_qty += $validated['cart_qty'];
                $cartItem->save();
                $message = 'Cart item updated successfully.';
            } else {
                // Create a new cart item
                Cart::create([
                    'food_item_id' => $validated['product_id'],
                    'cart_qty' => $validated['cart_qty'],
                    'user_id' => $validated['user_id']
                ]);
                $message = 'Cart item added successfully.';
            }

            // Optionally, reduce the stock of the food item
            $foodItem->stock -= $validated['cart_qty'];
            $foodItem->save();

            // Commit the transaction
            DB::commit();

            // Return a JSON response with a message and a success status
            return response()->json(['success' => true, 'message' => $message], 200);
        } catch (Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            // Return a JSON response with an error message and a failure status
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the cart.',
                'error' => $e->getMessage() // Optionally include the exception message
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            // Access user_id from the request body since it's a POST request
            $userId = $request->input('user_id');

            if ($userId) {
                $cartItems = Cart::where('user_id', $userId)->with('product')->get();
                if ($cartItems->isNotEmpty()) {

                    $cartItems->transform(function ($item) {
                        // Decode food_images if it's not already decoded
                        if (is_string($item->product->food_images)) {
                            $item->product->food_images = json_decode($item->product->food_images, true);
                        }
                        // Rename fooImages to food_images
                        $item->product->foodImages = $item->product->food_images ?? [];

                        return $item;
                    });

                    return response()->json([
                        'success' => true,
                        'data' => $cartItems->toArray(), // Convert collection to array
                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'No cart items found.',
                    ], 200);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching cart items.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
