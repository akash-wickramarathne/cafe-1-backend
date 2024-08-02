<?php

namespace App\Http\Controllers;

use App\Models\Auth\Waiter;
use App\Models\BookTable;
use App\Models\categories\FoodCategory;
use App\Models\Items\FoodItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function getOrderCount()
    {
        $orderCount = Order::count();

        return response()->json([
            'success' => true,
            'data' => $orderCount
        ], 200);
    }

    public function getClientCount()
    {
        //   $clientCount = Order::distinct('client_id')->count();
        $count = User::where('user_role_id', 1)->count();
        return response()->json([
            'success' => true,
            'data' => $count
        ], 200);
    }
    public function getOrdersPayAmount()
    {
        $totalAmount = Order::whereIn('order_status_id', [5])->sum('total_amount');
        return response()->json([
            'success' => true,
            'data' => $totalAmount
        ], 200);
    }

    public function getTableBooksPayAmount()
    {
        $totalAmount = BookTable::whereIn('table_status_id', [5])->sum('payment');
        return response()->json([
            'success' => true,
            'data' => $totalAmount
        ], 200);
    }

    public function getProductCount()
    {
        $count = FoodItem::count();
        return response()->json([
            'success' => true,
            'data' => $count
        ], 200);
    }
    public function getFoodCategoryCount()
    {
        $count = FoodCategory::count();
        return response()->json([
            'success' => true,
            'data' => $count
        ], 200);
    }

    public function getWaiterCount()
    {
        $count = Waiter::count();
        return response()->json([
            'success' => true,
            'data' => $count
        ], 200);
    }

    public function getTableCount()
    {
        $count = BookTable::whereIn('table_status_id', [2, 5])->count();
        return response()->json([
            'success' => true,
            'data' => $count
        ], 200);
    }

    // public function getBestSellingProduct(Request $request)
    // {
    //     try {
    //         // Fetch the best-selling products based on order quantity
    //         $bestSellingProductIds = OrderItem::select('food_item_id')
    //             ->selectRaw('SUM(quantity) as total_quantity')
    //             ->groupBy('food_item_id')
    //             ->orderBy('total_quantity', 'desc')
    //             ->pluck('food_item_id')
    //             ->toArray();

    //         // Fetch the details of the best-selling products
    //         $foodItemsQuery = FoodItem::with('foodCategory');

    //         if ($bestSellingProductIds) {
    //             $foodItemsQuery->whereIn('food_item_id', $bestSellingProductIds);
    //         }

    //         $foodItems = $foodItemsQuery->get();

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

    public function getBestSellingProduct(Request $request)
    {
        try {
            // Fetch the best-selling products based on order quantity
            $bestSellingProducts = OrderItem::select('food_item_id')
                ->selectRaw('SUM(quantity) as total_quantity')
                ->join('orders', 'order_items.order_id', '=', 'orders.order_id') // Join with the orders table
                ->where('orders.order_status_id', 5) // Filter for orders with status_id = 2
                ->groupBy('food_item_id')
                ->orderBy('total_quantity', 'desc') // Order by total quantity descending
                ->get(); // Get the complete result set

            // Fetch the details of the best-selling products
            $foodItemsQuery = FoodItem::with('foodCategory');

            if ($bestSellingProducts->isNotEmpty()) {
                $foodItemsQuery->whereIn('food_item_id', $bestSellingProducts->pluck('food_item_id'));
            }

            $foodItems = $foodItemsQuery->get();

            // Create a map for total quantities by food item ID
            $totalQuantities = $bestSellingProducts->keyBy('food_item_id')->map(function ($item) {
                return $item->total_quantity;
            });

            // Map the data to include order count and ensure proper order
            $data = $foodItems->map(function ($item) use ($totalQuantities) {
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
                    'orderCount' => $totalQuantities[$item->food_item_id] ?? 0, // Include the order count
                ];
            })->sortByDesc('orderCount'); // Sort the data by order count in descending order

            return response()->json([
                'success' => true,
                'data' => $data->values(), // Re-index the array after sorting
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ]);
        }
    }
}
