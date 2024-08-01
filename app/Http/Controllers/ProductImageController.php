<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductImageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048'
        ]);
        $imagesPaths = [];
        DB::beginTransaction();
        try {
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $name = 'product_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('products', $name, 'public');
                    $imagesPaths[] = $path;
                }
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Images Move Successfully',
                'paths' => $imagesPaths
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => 'Failed to upload images.'], 500);
        }
    }
}
