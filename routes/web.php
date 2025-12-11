<?php

use App\Http\Controllers\SliderController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ContactInfoController;
use App\Models\Category;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


Route::middleware(['throttle:60,1'])->group(function()
{

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }
    return view('admin.auth.login');
})->name('admin-login');

Route::post('/login', [UserController::class,'login'])->name('login');
Route::get('/logout', function () {
    Auth::logout();
    return redirect()->route('admin-login');
})->name('logout');

Route::get('/welcome', function () {
    return view('welcome');
});

Route::prefix('admin')->middleware(['auth' ,'role:admin'])->group(function () {

Route::put('/users/{id}/update-role', [UserController::class, 'updateUserRole'])
    ->name('users.updateRole');
    
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::post('/users/store', [UserController::class, 'store'])->name('users.store');

Route::delete('/stores/{id}/force', [StoreController::class, 'forceDeletestore'])->name('stores.forceDelete');
Route::delete('/stores/trash/empty', [StoreController::class, 'emptyTrash'])->name('stores.emptyTrash');

Route::delete('/products/trash/empty', [MealController::class, 'emptyTrash'])->name('products.emptyTrash');
Route::delete('/products/{id}/force', [MealController::class, 'forceDeleteMeal'])->name('products.forceDelete');

    Route::delete('/orders/{id}/force', [OrderController::class, 'forceDeleteorder'])->name('orders.forceDelete');
    Route::delete('/orders/trash/empty', [OrderController::class, 'emptyTrash'])->name('orders.emptyTrash');
});

Route::prefix('admin')->middleware(['auth' ,'role:admin|editor'])->group(function () {

Route::get('/dash', function () {
    // return view('admin.index');
    return redirect()->route('users');
})->name('home');

// Notifications
Route::prefix('notifications')->name('notifications.')->group(function () {

    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/send-to-all', [NotificationController::class, 'sendNotificationToAll'])
        ->name('sendtoall');
    Route::post('/send/{user_id}', [NotificationController::class, 'sendNotification'])
        ->name('send');

        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])
    ->name('unreadCount');


});

    // Users
    Route::get('/users', [UserController::class, 'index'])->name('users');
    
    
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::patch('/users/{id}/accept', [UserController::class, 'accept'])->name('users.accept');
    Route::patch('/users/{id}/ban', [UserController::class, 'ban'])->name('users.ban');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::delete('/users/{id}/force', [UserController::class, 'forceDeleteuser'])->name('users.forceDelete');
    Route::delete('/users/trash/empty', [UserController::class, 'emptyTrash'])->name('users.emptyTrash');
    Route::patch('/users/{id}/restore', [UserController::class, 'restoreTrasheduser'])->name('users.restore');


    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{id}/accept', [OrderController::class, 'accept'])->name('orders.accept');
    Route::patch('/orders/{order}/assign-delivery', [OrderController::class, 'assignDelivery'])->name('orders.assignDelivery');
    Route::delete('/orders/{id}/destroy', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::patch('/orders/{id}/force-status-change', [OrderController::class, 'forceStatusChange'])->name('orders.forceStatusChange');
    Route::post('/orders/{order}/reduce-delivery', [OrderController::class, 'reduceDelivery'])->name('orders.reduceDelivery');


    // Stores
Route::get('/stores', [StoreController::class, 'index'])->name('stores');
Route::get('/stores/{id}', [StoreController::class, 'show'])->name('stores.show');
Route::patch('/stores/{id}/accept', [StoreController::class, 'accept'])->name('stores.accept');
Route::patch('/stores/{id}/ban', [StoreController::class, 'ban'])->name('stores.ban');
Route::delete('/stores/{id}', [StoreController::class, 'destroy'])->name('stores.destroy');
Route::patch('/stores/{id}/restore', [StoreController::class, 'restoreTrashedstore'])->name('stores.restore');

// Products
Route::get('/products', [MealController::class, 'index'])->name('products');
Route::get('/products/{id}', [MealController::class, 'show'])->name('products.show');
Route::patch('/products/{id}/accept', [MealController::class, 'accept'])->name('products.accept');
Route::patch('/products/{id}/ban', [MealController::class, 'ban'])->name('products.ban');
Route::delete('/products/{id}', [MealController::class, 'destroy'])->name('products.destroy');
Route::patch('/products/{id}/restore', [MealController::class, 'restoreTrashedMeal'])->name('products.restore');


Route::prefix('coupons')->name('coupons.')->group(function () {

    Route::get('/', [CouponController::class, 'index'])->name('index');
    Route::get('/create', [CouponController::class, 'create'])->name('create');
    Route::post('/', [CouponController::class, 'store'])->name('store');
    Route::get('/{coupon}/edit', [CouponController::class, 'edit'])->name('edit');
    Route::patch('/{coupon}', [CouponController::class, 'update'])->name('update');
    Route::delete('/{coupon}', [CouponController::class, 'destroy'])->name('destroy');
    Route::post('/{coupon}/toggle', [CouponController::class, 'toggle'])->name('toggle');
    Route::post('/{coupon}/update-date', [CouponController::class, 'updateDate'])->name('updateDate');
});

Route::prefix('categories')->name('categories.')->group(function () {

    Route::get('/', [CategoryController::class, 'index'])->name('index');
    Route::get('/create', [CategoryController::class, 'create'])->name('create');
    Route::post('/', [CategoryController::class, 'store'])->name('store');
    Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
    Route::patch('/{category}', [CategoryController::class, 'update'])->name('update');
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
});

Route::prefix('sliders')->name('sliders.')->group(function () {

    Route::get('/', [SliderController::class, 'index'])->name('index');
    Route::get('/create', [SliderController::class, 'create'])->name('create');
    Route::post('/', [SliderController::class, 'store'])->name('store');
    Route::get('/{slider}/edit', [SliderController::class, 'edit'])->name('edit');
    Route::patch('/{slider}', [SliderController::class, 'update'])->name('update');
    Route::delete('/{slider}', [SliderController::class, 'destroy'])->name('destroy');
});

Route::prefix('supports')->group(function () {
    Route::get('/', [SupportController::class, 'index'])->name('admin.supports.index');
    Route::get('/{id}', [SupportController::class, 'show'])->name('admin.supports.show');
    Route::post('/{id}/reply', [SupportController::class, 'reply'])->name('admin.supports.reply');
    Route::post('/{id}/update-status', [SupportController::class, 'updateStatus'])->name('admin.supports.updateStatus');
    Route::delete('/{id}', [SupportController::class, 'destroy'])->name('admin.supports.destroy');
    Route::put('/{id}/close', [SupportController::class, 'close'])->name('admin.supports.close');
    // Route::get('/unread-count', [SupportController::class, 'unreadCount'])
    // ->name('supports.unreadCount');
   
});

Route::get('/closed', [SupportController::class, 'archive'])->name('admin.supports.closed');
//Route::get('/supports/unreadcount',[SupportController::class, 'unreadcount'])->name('supports.unreadCount');

Route::get('/contact-info', [ContactInfoController::class, 'edit'])->name('admin.contact.edit');
Route::post('/contact-info', [ContactInfoController::class, 'update'])->name('admin.contact.update');


});

});
