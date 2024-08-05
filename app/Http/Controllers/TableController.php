<?php

namespace App\Http\Controllers;

use App\Models\Tables;
use App\Models\TableSize;
use App\Models\TableStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TableController extends Controller
{
    public function getTableSizes()
    {
        try {
            $tables = TableSize::query()->get();

            $data = $tables->map(function ($table) {
                return [
                    'id' => $table->id,
                    'size' => $table->table_size,
                    'description' => $table->table_size_description
                ];
            });
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getTableStatuses()
    {
        try {
            $tables = TableStatus::query()->get();

            $data = $tables->map(function ($table) {
                return [
                    'id' => $table->id,
                    'status_name' => $table->status_name
                ];
            });
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function storeTable(Request $request)
    {
        // Define validation rules
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'size_id' => 'required|exists:table_sizes,id',
            'status_id' => 'required|exists:table_statuses,id',
            'seats' => 'required|integer|min:1',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create a new table record
            $table = Tables::create([
                'name' => $validatedData['name'],
                'size_id' => $validatedData['size_id'],
                'status_id' => $validatedData['status_id'],
                'seats' => $validatedData['seats'],
            ]);

            // Commit the transaction
            DB::commit();

            // Return a successful response
            return response()->json([
                'success' => true,
                'data' => $table
            ], 201);
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            // Return an error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the table.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
