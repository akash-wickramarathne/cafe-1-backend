<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BookTable;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class BookTableController extends Controller
{
    public function assignWaiter(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'tableId' => 'required|exists:book_tables,id',
            'waiterId' => 'required|exists:waiters,waiter_id',
        ]);

        // Find the book_table row by its primary key
        $bookTable = BookTable::find($validatedData['tableId']);

        // Update the waiter_id column
        $bookTable->waiter_id = $validatedData['waiterId'];
        $bookTable->table_status_id = 5;

        // Save the changes
        if ($bookTable->save()) {
            return response()->json([
                'success' => true,
                'message' => 'Waiter assigned successfully.',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign waiter.',
            ]);
        }
    }
    public function checkAvailability(Request $request)
    {
        try {
            // Validate the request data
            $request->validate([
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'date' => 'required|date_format:Y-m-d'
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Invalid data provided.', 'success' => false], 422);
        }

        // Create Carbon instances for the start and end times
        $startTime = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->start_time);
        $endTime = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->end_time);

        $overlappingBookings = BookTable::whereDate('start_time', $request->date)
            ->whereIn('table_status_id', [1, 2])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($query) use ($startTime, $endTime) {
                    // Check for overlapping conditions
                    $query->whereBetween('start_time', [$startTime, $endTime])
                        ->orWhereBetween('end_time', [$startTime, $endTime])
                        ->orWhere(function ($query) use ($startTime) {
                            // Allow the new booking to start at the same time an existing booking ends
                            $query->where('end_time', $startTime);
                        })
                        ->orWhere(function ($query) use ($startTime, $endTime) {
                            // Allow the new booking to start after the existing booking ends
                            $query->where('start_time', '<', $startTime) // Changed to '<'
                                ->where('end_time', '>=', $startTime); // Changed to '>='
                        });
                });
            })
            ->exists();


        if ($overlappingBookings) {
            return response()->json([
                'message' => 'You cannot book this time slot as it overlaps with an existing booking.',
                'success' => false
            ], 422);
        }

        return response()->json([
            'message' => 'The time slot is available for booking.',
            'success' => true
        ], 200);
    }



    // public function getBookTables(Request $request)
    // {
    //     try {
    //         // Fetch all book tables ordered by created_at in descending order
    //         // $bookTables = BookTable::orderBy('created_at', 'desc')->get();
    //         $bookTables = BookTable::with('user')->with('status')->orderBy('created_at', 'desc')->get();
    //         return response()->json([
    //             'success' => true,
    //             'data' => $bookTables
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An error occurred while fetching book tables.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function getBookTables(Request $request, $id = null)
    {
        try {
            if ($id) {
                $bookTable = BookTable::with('user')->with('status')->with('waiter')->find($id);

                if ($bookTable) {
                    return response()->json([
                        'success' => true,
                        'data' => $bookTable
                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Table not found.'
                    ], 404);
                }
            } else {
                $bookTables = BookTable::with('user')->with('status')->orderBy('created_at', 'desc')->get();
                return response()->json([
                    'success' => true,
                    'data' => $bookTables
                ], 200);
            }
            // Fetch all book tables ordered by created_at in descending order
            // $bookTables = BookTable::orderBy('created_at', 'desc')->get();

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching book tables.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getOrders(Request $request, $id = null)
    {
        try {
            if ($id) {
                // Fetch the order with the specific ID, including related OrderItems and FoodItems
                $order = Order::with('user')
                    ->with('status')
                    ->with(['orderItems.foodItem']) // Eager load FoodItems through OrderItems
                    ->find($id);

                if ($order) {
                    foreach ($order->orderItems as $orderItem) {
                        $foodItem = $orderItem->foodItem;
                        $foodItem->food_images = json_decode($foodItem->food_images, true);
                    }

                    return response()->json([
                        'success' => true,
                        'data' => $order
                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found.'
                    ], 404);
                }
            } else {
                // Fetch all orders with related OrderItems and FoodItems
                // $orders = Order::with('user')
                //     ->with('status')
                //     ->with(['orderItems.foodItem']) // Eager load FoodItems through OrderItems
                //     ->orderBy('created_at', 'desc')
                //     ->get();

                // foreach ($orders as $order) {
                //     foreach ($order->orderItems as $orderItem) {
                //         $foodItem = $orderItem->foodItem;
                //         $foodItem->food_images = json_decode($foodItem->food_images, true);
                //     }
                // }

                // return response()->json([
                //     'success' => true,
                //     'data' => $orders
                // ], 200);
                $orders = Order::with('user')
                    ->with('status')
                    ->with(['orderItems.foodItem']) // Eager load FoodItems through OrderItems
                    ->orderBy('created_at', 'desc')
                    ->get();

                foreach ($orders as $order) {
                    foreach ($order->orderItems as $orderItem) {
                        $foodItem = $orderItem->foodItem;

                        // Check if food_images is a string before decoding
                        if (is_string($foodItem->food_images)) {
                            $foodItem->food_images = json_decode($foodItem->food_images, true);
                        }
                        // If food_images is already an array, no need to decode
                    }
                }

                return response()->json([
                    'success' => true,
                    'data' => $orders
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching orders.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
