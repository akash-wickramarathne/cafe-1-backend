<?php

// namespace App\Http\Controllers\Foods;

// use App\Http\Controllers\Controller;
// use App\Http\Requests\AddFoodCategoryRequest;
// use App\Models\categories\FoodCategory;
// use App\Services\AuthenticateService;
// use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;
// use Illuminate\Http\Response;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\DB;

// class FoodCategoryController extends Controller
// {
//     protected $authenticateService;

//     /**
//      * Inject AuthenticateService into the controller.
//      *
//      * @param AuthenticateService $authenticateService
//      */
//     public function __construct(AuthenticateService $authenticateService)
//     {
//         $this->authenticateService = $authenticateService;
//     }
//     public function index()
//     {
//         try {
//             $foodCategories = FoodCategory::query();

//             $data = $foodCategories->get();

//             return response()->json([
//                 'status' => true,
//                 'data' => $data,
//             ], Response::HTTP_OK);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'status' => false,
//                 'message' => $e->getMessage(),
//             ], Response::HTTP_INTERNAL_SERVER_ERROR);
//         }
//     }

//     public function store(AddFoodCategoryRequest $request): JsonResponse
//     {
//         DB::beginTransaction();

//         try {
//             // Retrieve validated data from the request
//             $data = $request->validated();

//             // Get the authenticated admin ID
//             $createAdminId = Auth::id();
//             if ($createAdminId === null) {
//                 DB::rollBack();
//                 return response()->json([
//                     'success' => false,
//                     'message' => 'Admin not authenticated.',
//                 ], 403);
//             }

//             // Create a new food category
//             $foodCategory = FoodCategory::create([
//                 'food_category_name' => $data['food_category_name'],
//                 'food_category_description' => $data['food_category_description'],
//                 'create_admin_id' => $createAdminId,
//             ]);

//             DB::commit();

//             return response()->json([
//                 'success' => true,
//                 'data' => $foodCategory,
//                 'message' => 'Food category created successfully!',
//             ]);
//         } catch (\Throwable $th) {
//             DB::rollBack();
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Failed to create food category.',
//                 'error' => $th->getMessage(),
//             ], 500);
//         }
//     }

// }



namespace App\Http\Controllers;

use App\Http\Requests\AddFoodCategoryRequest;
use App\Models\categories\FoodCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class FoodCategoryController extends Controller
{
    public function index()
    {
        try {
            // Retrieve all food categories
            $foodCategories = FoodCategory::query()->get();

            // Transform the data to rename keys
            $data = $foodCategories->map(function ($category) {
                return [
                    'id' => $category->food_category_id,
                    'name' => $category->food_type_name,
                    'description' => $category->food_type_description,
                    'totalItems' => 0
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

    public function store(AddFoodCategoryRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Retrieve validated data from the request
            $data = $request->validated();

            // Get the authenticated admin ID
            $createAdminId = 1;
            if ($createAdminId === null) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Admin not authenticated.',
                ], 403);
            }

            // Create a new food category
            $foodCategory = FoodCategory::create([
                'food_type_name' => $data['food_category_name'],
                'food_type_description' => $data['food_category_description'],
                'create_admin_id' => $createAdminId,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $foodCategory,
                'message' => 'Food category created successfully!',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create food category.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
