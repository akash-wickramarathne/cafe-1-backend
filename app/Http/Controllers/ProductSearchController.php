<?php

// namespace App\Http\Controllers;

// use App\Models\Items\FoodItem;
// use Illuminate\Http\Request;
// use Illuminate\Http\JsonResponse;
// use Illuminate\Support\Facades\DB;
// use Exception;

// class ProductSearchController extends Controller
// {

//     public function searchProduct(Request $request)
//     {
//         $query = $request->query('query');
//         $minPrice = $request->query('minPrice');
//         $maxPrice = $request->query('maxPrice');
//         $sort = $request->query('sort');
//         $categoryId = $request->query('category_id');
//         $brandId = $request->query('brand_id');
//         $perPage = $request->query('perPage', 2);


//         // Perform the search based on the query parameters
//         $products = FoodItem::query()
//             ->when($query, function ($queryBuilder) use ($query) {
//                 return $queryBuilder->where('food_name', 'like', '%' . $query . '%');
//             })
//             ->when($minPrice, function ($queryBuilder) use ($minPrice) {
//                 return $queryBuilder->whereRaw('CAST(price AS DECIMAL(10,2)) >= ' . $minPrice);
//             })
//             ->when($maxPrice, function ($queryBuilder) use ($maxPrice) {
//                 return $queryBuilder->whereRaw('CAST(price AS DECIMAL(10,2)) <= ' . $maxPrice);
//             })
//             ->when($categoryId, function ($queryBuilder) use ($categoryId) {
//                 return $queryBuilder->where('food_category_id', $categoryId);
//             })
//             // ->when($brandId, function ($queryBuilder) use ($brandId) {
//             //     return $queryBuilder->where('product_brand_id', $brandId);
//             // })
//             ->when($sort, function ($queryBuilder) use ($sort) {
//                 $sortParts = explode(':', $sort);
//                 if (count($sortParts) == 2) {
//                     $field = $sortParts[0];
//                     if ($field === 'name') {
//                         $field = 'title';
//                     }
//                     $direction = $sortParts[1];
//                     return $queryBuilder->orderBy($field, $direction);
//                 }
//                 // Handle default sorting if necessary
//             })
//             ->paginate($perPage);

//         $data = $products->map(function ($item) {
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
//         return response()->json(['products' => $data]);
//     }
// }



namespace App\Http\Controllers;

use App\Models\Items\FoodItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Exception;

class ProductSearchController extends Controller
{
    public function searchProduct(Request $request)
    {
        $query = $request->query('query');
        $minPrice = $request->query('minPrice');
        $maxPrice = $request->query('maxPrice');
        $sort = $request->query('sort');
        $categoryId = $request->query('category_id');
        $brandId = $request->query('brand_id');
        $perPage = $request->query('perPage', 3);

        // Perform the search based on the query parameters
        $products = FoodItem::query()
            ->when($query, function ($queryBuilder) use ($query) {
                return $queryBuilder->where('food_name', 'like', '%' . $query . '%');
            })
            ->when($minPrice, function ($queryBuilder) use ($minPrice) {
                return $queryBuilder->whereRaw('CAST(price AS DECIMAL(10,2)) >= ?', [$minPrice]);
            })
            ->when($maxPrice, function ($queryBuilder) use ($maxPrice) {
                return $queryBuilder->whereRaw('CAST(price AS DECIMAL(10,2)) <= ?', [$maxPrice]);
            })
            ->when($categoryId, function ($queryBuilder) use ($categoryId) {
                return $queryBuilder->where('food_category_id', $categoryId);
            })
            ->when($brandId, function ($queryBuilder) use ($brandId) {
                return $queryBuilder->where('product_brand_id', $brandId);
            })
            ->when($sort, function ($queryBuilder) use ($sort) {
                $sortParts = explode(':', $sort);
                if (count($sortParts) == 2) {
                    $field = $sortParts[0];
                    if ($field === 'name') {
                        $field = 'title';
                    }
                    $direction = $sortParts[1];
                    return $queryBuilder->orderBy($field, $direction);
                }
                // Handle default sorting if necessary
            })
            ->paginate($perPage);

        $data = $products->map(function ($item) {
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
            'products' => $data,
            'pagination' => [
                'total' => $products->total(),
                'count' => $products->count(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'total_pages' => $products->lastPage()
            ]
        ]);
    }
}
