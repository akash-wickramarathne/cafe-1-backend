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
        $totalAmount = Order::whereIn('order_status_id', [2, 5])->sum('total_amount');
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

    public function getBestSellingProduct(Request $request)
    {
        try {
            // Fetch the best-selling products based on order quantity
            $bestSellingProductIds = OrderItem::select('food_item_id')
                ->selectRaw('SUM(quantity) as total_quantity')
                ->groupBy('food_item_id')
                ->orderBy('total_quantity', 'desc')
                ->pluck('food_item_id')
                ->toArray();

            // Fetch the details of the best-selling products
            $foodItemsQuery = FoodItem::with('foodCategory');

            if ($bestSellingProductIds) {
                $foodItemsQuery->whereIn('food_item_id', $bestSellingProductIds);
            }

            $foodItems = $foodItemsQuery->get();

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
}
