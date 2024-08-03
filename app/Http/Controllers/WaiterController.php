<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Auth\Waiter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class WaiterController extends Controller
{
    // public function storeWaiter(Request $request)
    // {
    //     // Validate the input
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|email|max:255',
    //         'phone' => 'required|string|max:15',
    //     ]);

    //     // Check if the email exists in the users table
    //     $user = User::where('email', $request->email)->first();

    //     if ($user) {
    //         // If user exists, update the user_role_id to 3
    //         if ($user->user_role_id === 4) {
    //             return response()->json(['message' => 'This waiter is already registered.','success' => false]);
    //         }
    //         $user->update(['user_role_id' => 4]);

    //         // Get the user's password
    //         $password = $user->password;
    //     } else {
    //         // If user does not exist, validate the password
    //         $request->validate([
    //             'password' => 'required|string|min:8',
    //         ]);

    //         // Hash the password
    //         $password = Hash::make($request->password);

    //         // Create a new user with user_role_id 3
    //         $user = User::create([
    //             'name' => $request->name,
    //             'email' => $request->email,
    //             'phone_number' => $request->phone,
    //             'password' => $password,
    //             'user_role_id' => 4,
    //         ]);
    //     }

    //     // Create a new waiter associated with the user
    //     Waiter::create([
    //         'user_id' => $user->id,
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'phone' => $request->phone,
    //         // Add other waiter-specific fields here
    //     ]);

    //     return response()->json(['message' => 'Waiter saved successfully.','success' => true]);
    // }

    public function index()
    {
        try {
            $waiters = Waiter::query()->get();

            // Transform the data to rename keys
            $data = $waiters->map(function ($waiter) {
                return [
                    'waiter_id' => $waiter->waiter_id,
                    'name' => $waiter->name,
                    'email' => $waiter->email
                ];
            });

            return response()->json([
                'status' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function storeWaiter(Request $request)
    {
        // Validate the input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:15',
            'password' => 'required|string|min:8', // Make password required for both cases
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Check if the email exists in the users table
            $user = User::where('email', $request->email)->first();

            if ($user) {
                // If user exists, check user_role_id
                if ($user->user_role_id === 4) {
                    return response()->json(['message' => 'This waiter is already registered.', 'success' => false]);
                }

                // Update the user_role_id to 4
                $user->update(['user_role_id' => 4]);

                // Get the user's password (you can skip this if you don't need the password)
                $password = $user->password;
            } else {
                // If user does not exist, hash the password
                $password = Hash::make($request->password);

                // Create a new user with user_role_id 4
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone_number' => $request->phone,
                    'password' => $password,
                    'user_role_id' => 4,
                ]);
            }

            // Create a new waiter associated with the user
            Waiter::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                // Add other waiter-specific fields here
            ]);

            // Commit the transaction
            DB::commit();

            return response()->json(['message' => 'Waiter saved successfully.', 'success' => true]);
        } catch (\Exception $e) {
            // Rollback the transaction if any error occurs
            DB::rollBack();

            return response()->json(['message' => 'An error occurred: ' . $e->getMessage(), 'success' => false], 500);
        }
    }
}
