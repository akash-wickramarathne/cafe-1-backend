<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddWishlistRequest;
use App\Models\Cart;
use App\Models\Items\FoodItem;
use App\Models\WishList;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Exception;

class WishlistController extends Controller
{
    /**
     * Store or update the cart item.
     *
     * @param  \App\Http\Requests\AddorUpdateCartRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(AddWishlistRequest $request): JsonResponse
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



            // Check if the cart item already exists
            $wishlistItem = WishList::where('food_item_id', $validated['product_id'])
                ->where('user_id', $validated['user_id'])
                ->first();

            if (!$wishlistItem) {
                WishList::create([
                    'food_item_id' => $validated['product_id'],
                    'user_id' => $validated['user_id']
                ]);
                $message = 'Item wishlist add successfully.';
            } else {
                $message = 'You have already add this to wishlist.';
            }



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
                'message' => 'An error occurred while updating the wishlist.',
                'error' => $e->getMessage() // Optionally include the exception message
            ], 500);
        }
    }
}
