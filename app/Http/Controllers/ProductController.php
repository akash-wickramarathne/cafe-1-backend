<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ProductStoreRequest;
use App\Models\Items\FoodItem;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $productId = $request->query('product_id');
            $foodItemIds = $request->query('food_item_ids');
            $query = FoodItem::with('foodCategory');

            if ($productId) {
                // Return only the specific product
                $foodItem = $query->where('food_item_id', $productId)->first();

                if ($foodItem) {
                    $data = [
                        'id' => $foodItem->food_item_id,
                        'name' => $foodItem->food_name,
                        'description' => $foodItem->description,
                        'price' => $foodItem->price,
                        'stock' => $foodItem->stock,
                        'foodImages' => json_decode($foodItem->food_images, true),
                        'category' => [
                            'id' => $foodItem->foodCategory->food_category_id,
                            'name' => $foodItem->foodCategory->food_type_name,
                        ],
                    ];

                    return response()->json([
                        'success' => true,
                        'data' => $data,
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not found',
                    ]);
                }
            } else if ($foodItemIds) {
                // Return products by IDs
                $foodItemIdsArray = explode(',', $foodItemIds);
                $query->whereIn('food_item_id', $foodItemIdsArray);
            }

            // Return all products or filtered by IDs
            $foodItems = $query->get();

            $data = $foodItems->map(function ($item) {
                return [
                    'id' => $item->food_item_id,
                    'name' => $item->food_name,
                    'description' => $item->description,
                    'price' => $item->price,
                    'stock' => $item->stock,
                    'foodImages' => json_decode($item->food_images, true),
                    'category' => [
                        'id' => $item->foodCategory->food_category_id,
                        'name' => $item->foodCategory->food_type_name,
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ]);
        }
    }

    // public function index(Request $request)
    // {
    //     try {
    //         $foodItemIds = $request->query('food_item_ids');
    //         $query = FoodItem::with('foodCategory');

    //         if ($foodItemIds) {
    //             $foodItemIdsArray = explode(',', $foodItemIds);
    //             $query->whereIn('food_item_id', $foodItemIdsArray);
    //         }

    //         $foodItems = $query->get();

    //         $data = $foodItems->map(function ($item) {
    //             return [
    //                 'id' => $item->food_item_id,
    //                 'name' => $item->food_name,
    //                 'description' => $item->description,
    //                 'price' => $item->price,
    //                 'stock' => $item->stock,
    //                 'foodImages' => json_decode($item->food_images, true),
    //                 'category' => [
    //                     'id' => $item->foodCategory->food_category_id,
    //                     'name' => $item->foodCategory->food_type_name,
    //                 ],
    //             ];
    //         });

    //         return response()->json([
    //             'success' => true,
    //             'data' => $data,
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An error occurred: ' . $e->getMessage(),
    //         ]);
    //     }
    // }

    //     public function index(Request $request)
    // {
    //     try {
    //         $foodItemIds = $request->query('food_item_ids');

    //         if ($foodItemIds) {
    //             $foodItemIdsArray = explode(',', $foodItemIds);
    //             $foodItems = FoodItem::with('foodCategory')
    //                 ->whereIn('food_item_id', $foodItemIdsArray)
    //                 ->get();

    //             $data = $foodItems->map(function ($item) {
    //                 return [
    //                     'id' => $item->food_item_id,
    //                     'name' => $item->food_name,
    //                     'description' => $item->description,
    //                     'price' => $item->price,
    //                     'stock' => $item->stock,
    //                     'foodImages' => json_decode($item->food_images, true),
    //                     'category' => [
    //                         'id' => $item->foodCategory->food_category_id,
    //                         'name' => $item->foodCategory->food_type_name,
    //                     ],
    //                 ];
    //             });

    //             return response()->json([
    //                 'success' => true,
    //                 'data' => $data,
    //             ]);
    //         } else {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'No food item IDs provided',
    //             ], 400);
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An error occurred: ' . $e->getMessage(),
    //         ]);
    //     }
    // }

    // public function index(Request $request)
    // {
    //     try {
    //         $foodItemId = $request->query('food_item_id');

    //         if ($foodItemId) {
    //             $foodItem = FoodItem::with('foodCategory')->find($foodItemId);

    //             if (!$foodItem) {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'Food item not found',
    //                 ], 404);
    //             }

    //             $data = [
    //                 'id' => $foodItem->food_item_id,
    //                 'name' => $foodItem->food_name,
    //                 'description' => $foodItem->description,
    //                 'price' => $foodItem->price,
    //                 'stock' => $foodItem->stock,
    //                 'foodImages' => json_decode($foodItem->food_images, true),
    //                 'category' => [
    //                     'id' => $foodItem->foodCategory->food_category_id,
    //                     'name' => $foodItem->foodCategory->food_type_name,
    //                 ],
    //             ];

    //             return response()->json([
    //                 'success' => true,
    //                 'data' => $data,
    //             ]);
    //         } else {
    //             $foodItems = FoodItem::with('foodCategory')->get();

    //             $data = $foodItems->map(function ($item) {
    //                 return [

    //                     'id' => $item->food_item_id,
    //                     'name' => $item->food_name,
    //                     'description' => $item->description,
    //                     'price' => $item->price,
    //                     'stock' => $item->stock,
    //                     'foodImages' => json_decode($item->food_images, true),
    //                     'category' => [
    //                         'id' => $item->foodCategory->food_category_id,
    //                         'name' => $item->foodCategory->food_type_name,
    //                     ],
    //                 ];
    //             });

    //             return response()->json([
    //                 'success' => true,
    //                 'data' => $data,
    //             ]);
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An error occurred: ' . $e->getMessage(),
    //         ]);
    //     }
    // }


    public function store(ProductStoreRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Retrieve validated data from the request
            $data = $request->validated();

            // Get the authenticated admin ID (replace with actual logic)
            // $createAdminId = 1;

            // if ($createAdminId === null) {
            //     DB::rollBack();
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Admin not authenticated.',
            //     ], 403);
            // }

            // Create a new food item
            $foodItem = FoodItem::create([
                'food_name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'],
                'food_images' => json_encode($data['imagePaths']), // Convert array to JSON string
                'create_admin_id' => 1,
                'food_category_id' => $data['category'], // Ensure this matches your database column
            ]);




            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $foodItem,
                'message' => 'Food item created successfully!',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create food item.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
