<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FoodCategoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\WishlistController;
use App\Http\Middleware\EnsureAdminAuth;
use App\Http\Middleware\EnsureClientAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookTableController;
use App\Http\Controllers\ProductSearchController;
use App\Http\Controllers\WaiterController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->get('/user-role', function (Request $request) {
    return $request->user()->user_role_id;
});

Route::get('/get/foodCategoires', [FoodCategoryController::class, 'index']);
Route::get('/get/waiters', [WaiterController::class, 'index']);

//Admin Function

//Store data by admin
Route::middleware(['auth:sanctum', EnsureAdminAuth::class])->prefix('/admin')->group(function () {
});
//Route::post('/store/foodCategory', [FoodCategoryController::class, 'store']);

Route::prefix('admin')->middleware([EnsureAdminAuth::class])->group(function () {
    Route::post('/store/foodCategory', [FoodCategoryController::class, 'store']);
    Route::post('/store/product/image', [ProductImageController::class, 'store']);
    Route::post('/store/product', [ProductController::class, 'store']);
    Route::post('/store/waiters', [WaiterController::class, 'storeWaiter']);
    Route::get('/get/book-tables/{id?}', [BookTableController::class, 'getBookTables']);
    Route::get('/get/orders/{id?}', [BookTableController::class, 'getOrders']);
    Route::post('/assign-waiter', [BookTableController::class, 'assignWaiter']);
    Route::get('/dashboard/count/clients', [AdminDashboardController::class, 'getClientCount']);
    Route::get('/dashboard/count/orders', [AdminDashboardController::class, 'getOrderCount']);
    Route::get('/dashboard/count/products', [AdminDashboardController::class, 'getProductCount']);
    Route::get('/dashboard/count/waiters', [AdminDashboardController::class, 'getWaiterCount']);
    Route::get('/dashboard/count/tables', [AdminDashboardController::class, 'getTableCount']);
    Route::get('/dashboard/count/foodCategories', [AdminDashboardController::class, 'getFoodCategoryCount']);
    Route::get('/dashboard/amount/orders', [AdminDashboardController::class, 'getOrdersPayAmount']);
    Route::get('/dashboard/amount/tables', [AdminDashboardController::class, 'getTableBooksPayAmount']);
    Route::put('/product/edit/{id}', [ProductController::class, 'update']);
});

Route::get('/get/products', [ProductController::class, 'index']);
Route::put('/product/edit/{id}', [ProductController::class, 'update']);


//Only client apis
Route::prefix('client')->middleware(['auth:sanctum', EnsureClientAuth::class])->group(function () {
    Route::post('/add/cart/product', [CartController::class, 'store']);
    Route::post('/add/wishlist/product', [WishlistController::class, 'store']);
    Route::post('/get/cart/all', [CartController::class, 'index']);
    Route::post('/make-payment', [PaymentController::class, 'makePayment']);
    Route::post('/make-payment/table', [PaymentController::class, 'makePaymentBookTable']);
    Route::post('/update-payment-status', [PaymentController::class, 'updatePaymentStatus']);
});


Route::post('/check-availability/table', [BookTableController::class, 'checkAvailability']);
Route::get('/get/best-selling/product', [AdminDashboardController::class, 'getBestSellingProduct']);
Route::get('/search', [ProductSearchController::class, 'searchProduct']);
