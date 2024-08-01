<?php

// namespace App\Http\Controllers;

// use App\Models\Items\FoodItem;
// use App\Models\Order;
// use App\Models\OrderItem;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

// class PaymentController extends Controller
// {
//     public function makePayment(Request $request)
//     {
//         $totalAmounts = 0;
//         $totalItems = [];

//         foreach ($request->order_items as $item) {
//             $product = FoodItem::find($item['product_id']);
//             if ($product) {
//                 $totalAmounts += $product->price * $item['quantity'];
//             }
//         }
//         $order = Order::create();
//         $order->user_id = 3;
//         $order->total_amount = $totalAmounts;
//         $order->order_status_id = 1;
//         $order->save();

//         foreach ($request->order_items as $item) {
//             $product = FoodItem::find($item['product_id']);
//             if ($product) {
//                 $orderItem = new OrderItem();
//                 $orderItem->order_id = $order->id;
//                 $orderItem->food_item_id = $product->food_item_id;
//                 $orderItem->quantity = $item['quantity'];
//                 $orderItem->price = $product->price;
//                 $orderItem->save();

//                 $priceInCents = $product->price * 100;
//                 $lineItems[] = [
//                     'price_data' => [
//                         'currency' => 'LKR',
//                         'unit_amount' => $priceInCents,
//                         'product_data' => [
//                             'name' => $product->food_name,
//                             'description' => $product->description
//                         ]
//                     ],
//                     'quantity' => $item['quantity']
//                 ];
//             }
//         }

//         // Set Stripe API key
//         \Stripe\Stripe::setApiKey(config('stripe.secret_key'));

//         // Create a Stripe Checkout session
//         $session = \Stripe\Checkout\Session::create([
//             'payment_method_types' => ['card'],
//             'line_items' => $lineItems,
//             'mode' => 'payment',
//             'success_url' => 'http://localhost:3000/payment?session_id={CHECKOUT_SESSION_ID}',
//             'cancel_url' => 'http://localhost:3000/payment/cancel',
//             'shipping_address_collection' => [
//                 'allowed_countries' => ['US', 'CA', 'LK'] // Include the countries you want to allow
//             ]
//         ]);

//         $order->stripe_session_id = $session->id;
//         $order->save();
//         return response()->json([
//             'url' => $session->url
//         ]);
//     }
// }

namespace App\Http\Controllers;

use App\Models\BookTable;
use App\Models\Items\FoodItem;
use App\Models\Order;
use App\Models\OrderItem;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function makePayment(Request $request)
    {
        $totalAmounts = 0;
        $lineItems = [];

        // Calculate total amount and prepare line items for Stripe
        foreach ($request->order_items as $item) {
            $product = FoodItem::find($item['product_id']);
            if ($product) {
                $totalAmounts += $product->price * $item['quantity'];
            }
        }

        // Create the order and save it to the database
        $order = Order::create([
            'user_id' => Auth::id(),
            'total_amount' => $totalAmounts,
            'order_status_id' => 1,
        ]);

        // Check if the order was created successfully
        if (!$order) {
            return response()->json(['error' => 'Order creation failed'], 500);
        }

        // Debugging: Log the order details
        //  \Log::info('Order created:', ['order_id' => $order->order_id]);

        // Iterate through order items and create order items in the database
        foreach ($request->order_items as $item) {
            $product = FoodItem::find($item['product_id']);
            if ($product) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->order_id; // Use the primary key of the newly created order
                $orderItem->food_item_id = $product->food_item_id; // Assuming 'food_item_id' is the primary key for food items
                $orderItem->quantity = $item['quantity'];
                $orderItem->price = $product->price;

                // Debugging: Log the order item details before saving
                // \Log::info('Creating order item:', [
                //     'order_id' => $orderItem->order_id,
                //     'food_item_id' => $orderItem->food_item_id,
                //     'quantity' => $orderItem->quantity,
                //     'price' => $orderItem->price,
                // ]);

                $orderItem->save();

                // Prepare line items for Stripe
                $priceInCents = $product->price * 100;
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'LKR',
                        'unit_amount' => $priceInCents,
                        'product_data' => [
                            'name' => $product->food_name,
                            'description' => $product->description
                        ]
                    ],
                    'quantity' => $item['quantity']
                ];
            }
        }

        // Set Stripe API key
        \Stripe\Stripe::setApiKey(config('stripe.secret_key'));

        // Create a Stripe Checkout session
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => 'http://localhost:3000/payment?session_id={CHECKOUT_SESSION_ID}&type=1',
            'cancel_url' => 'http://localhost:3000/payment/cancel',
            'shipping_address_collection' => [
                'allowed_countries' => ['US', 'CA', 'LK'] // Include the countries you want to allow
            ]
        ]);

        // Save the Stripe session ID to the order
        $order->stripe_session_id = $session->id;
        $order->save();

        return response()->json([
            'url' => $session->url
        ]);
    }

    public function makePaymentBookTable(Request $request)
    {
        // Check if all necessary parameters are present
        if (!$request->has(['date', 'start_time', 'end_time'])) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        // Extract parameters from the request
        $date = $request->input('date');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        // Convert times to DateTime objects
        $startDateTime = new DateTime("$date $startTime");
        $endDateTime = new DateTime("$date $endTime");

        // Check if the end time is greater than the start time
        if ($endDateTime <= $startDateTime) {
            return response()->json(['error' => 'End time must be greater than start time'], 400);
        }

        // Check for overlapping bookings
        $overlappingBookings = BookTable::where('book_date', $date)
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->whereBetween('start_time', [$startDateTime, $endDateTime])
                    ->orWhereBetween('end_time', [$startDateTime, $endDateTime])
                    ->orWhere(function ($query) use ($startDateTime, $endDateTime) {
                        $query->where('start_time', '<', $startDateTime)
                            ->where('end_time', '>', $endDateTime);
                    });
            })
            ->exists();

        if ($overlappingBookings) {
            return response()->json(['error' => "You can't book this table at the specified time"], 409);
        }

        // Calculate total duration in minutes
        $duration = $startDateTime->diff($endDateTime);
        $totalMinutes = ($duration->h * 60) + $duration->i;

        // Calculate total amount (60 minutes charge is 6000)
        $totalAmount = ($totalMinutes / 60) * 6000;

        // Prepare line items for Stripe
        $lineItems = [];

        $bookTable = BookTable::create([
            'user_id' => Auth::id(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'book_date' => $date,
            'payment' => $totalAmount,
            'table_status_id' => 1
        ]);

        if (!$bookTable) {
            return response()->json(['error' => 'Table Book creation failed'], 500);
        }


        // Add booking charge as a line item
        $lineItems[] = [
            'price_data' => [
                'currency' => 'LKR',
                'unit_amount' => $totalAmount * 100, // amount in cents
                'product_data' => [
                    'name' => 'Booking Charge',
                    'description' => "Booking charge for $totalMinutes minutes",
                ],
            ],
            'quantity' => 1,
        ];


        // Set Stripe API key
        \Stripe\Stripe::setApiKey(config('stripe.secret_key'));

        // Create a Stripe Checkout session
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => 'http://localhost:3000/payment?session_id={CHECKOUT_SESSION_ID}&type=2',
            'cancel_url' => 'http://localhost:3000/payment/cancel',
            'shipping_address_collection' => [
                'allowed_countries' => ['US', 'CA', 'LK'], // Include the countries you want to allow
            ],
        ]);

        $bookTable->stripe_session_id = $session->id;
        $bookTable->save();

        return response()->json([
            'url' => $session->url,
            'success' => true
        ]);
    }


    public function updatePaymentStatus(Request $request)
    {
        $stripeSessionId = $request->stripe_session_id;
        $type = $request->type;

        if ($type == 1) {
            $order = Order::where('stripe_session_id', $stripeSessionId)->first();

            // Check if the order exists
            if ($order) {
                // Update the order_status_id to 2
                $order->order_status_id = 2;

                // Save the changes
                $order->save();

                return response()->json([
                    'message' => 'Payment status updated successfully.',
                    'order' => $order
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Order not found.'
                ], 404);
            }
        }
        if ($type == 2) {
            $table = BookTable::where('stripe_session_id', $stripeSessionId)->first();

            // Check if the order exists
            if ($table) {
                // Update the order_status_id to 2
                $table->table_status_id = 2;

                // Save the changes
                $table->save();

                return response()->json([
                    'message' => 'Payment status updated successfully.',
                    'order' => $table
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Table not found.'
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'Invalid Type.'
            ], 404);
        }

        // Find the order by stripe_session_id

    }
}
