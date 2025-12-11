<?php

use App\Http\Controllers\Api\AdditionalController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MealController;
use App\Http\Controllers\Api\NewOrderController;
use App\Http\Controllers\Api\NewPaymentController;
use App\Http\Controllers\Api\SliderController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\ContactInfoController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\PasswordCodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Events\SendNotification;



Route::middleware(['throttle:10,1'])->group(function()
{
Route::post('/register', [UserController::class,'register']);
Route::post('/login', [UserController::class,'login']);

Route::get('/getPublishableKey', [NewPaymentController::class,'getPublishableKey']);


Route::post('/forgot-password', [PasswordCodeController::class, 'sendCode']);
Route::post('/verify-code', [PasswordCodeController::class, 'verifyCode']);
Route::post('/reset-password', [NewPasswordController::class, 'resetPassword']);

Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');


Route::middleware(['auth:sanctum'])->post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verification link sent!']);
})->name('verification.send');

});

Route::middleware(['auth:sanctum', 'throttle:120,1' , 'email.verified', 'CheckUserStatus'])->group(function()
{
// Route::post('/save-fcm-token', [UserController::class,'saveFcmToken']);
Route::post('/logout', [UserController::class,'logout']);
Route::get('/getNotifications', [UserController::class,'getNotifications']);
Route::get('/markAsRead/{notification_id}', [UserController::class,'markAsRead']);
Route::get('/markAllAsRead', [UserController::class,'markAllAsRead']);



Route::get('/profile', [UserController::class,'getProfile']);
Route::post('/updateProfile', [UserController::class,'updateProfile']);

Route::get('/slider', [SliderController::class,'getSlider']);
Route::get('/category', [CategoryController::class,'getCategory']);

Route::get('/myStores', [StoreController::class,'myStores']);
Route::post('/addstore', [StoreController::class,'addstore']);
Route::delete('/deletestore/{store_id}', [StoreController::class,'deletestore']);
Route::post('/editstore/{store_id}', [StoreController::class,'editstore']);
Route::get('/getCategorystores/{category_id}', [StoreController::class,'getCategoryStores']);
Route::post('/storeAppeal/{store_id}', [StoreController::class,'appeal']);


Route::get('/profilestore/{store_id}', [StoreController::class,'profilestore']);
Route::get('/follow/{storeId}', [StoreController::class, 'follow']);
Route::get('/unfollow/{storeId}', [StoreController::class, 'unfollow']);
Route::get('/checkfollow/{storeId}', [StoreController::class, 'checkfollow']);
Route::get('/followedStores', [StoreController::class, 'followedStores']);
Route::post('/rateStore/{store_id}', [RatingController::class, 'rateStore']);




Route::prefix('meals')->group(function () {

    Route::post('/addmeal', [MealController::class,'addmeal']);
    Route::get('/getmeal/{meal_id}', [MealController::class,'getmeal']);
    Route::post('/editmeal/{meal_id}', [MealController::class,'editmeal']);
    Route::get('/getstoremeals/{store_id}', [MealController::class,'getstoremeals']);
    Route::get('/mostSellingmeals/{store_id}', [MealController::class,'mostSellingmeals']);
    Route::get('/getwaitingmeals/{store_id}', [MealController::class,'getwaitingmeals']);
    Route::get('/getBanedgmeals/{store_id}', [MealController::class,'getBanedgmeals']);

    Route::get('/gethidden/{store_id}', [MealController::class, 'hiddenMeals']);
    Route::get('/trashed/{store_id}', [MealController::class, 'trashedMeals']);
    Route::get('/hide/{meal_id}', [MealController::class, 'hideMeal']);
    Route::get('/restorehidden/{meal_id}', [MealController::class, 'restoreHiddenMeal']);
    Route::delete('/softdelete/{meal_id}', [MealController::class, 'softDeleteMeal']);
    //Route::post('/restoretrashed/{meal_id}', [MealController::class, 'restoreTrashedMeal']);
    //Route::delete('/forcedelete/{meal_id}', [MealController::class, 'forceDeleteMeal']); // للأدمن

    Route::get('/countTrashed/{storeId}', [MealController::class, 'countTrashed']);
    Route::post('/mealAppeal/{meal_id}', [MealController::class,'appeal']);
});


Route::post('/addAdditional', [AdditionalController::class,'addAdditional']);
Route::get('/getStoreAdditional/{store_id}', [AdditionalController::class,'getStoreAdditional']);
Route::delete('/deleteadditional/{additionalId}', [AdditionalController::class,'deleteadditional']);
Route::post('/editadditional/{additionalId}', [AdditionalController::class,'editadditional']);


Route::prefix('cart')->group(function(){
Route::get('/getItems', [CartController::class,'getItems']);
//Route::get('/getItem/{cartItemId}', [CartController::class,'getItem']);
Route::post('/addItem/{orderId}', [CartController::class,'addItem']);
Route::post('/updateItem/{cartItemId}', [CartController::class,'updateItem']);
Route::delete('/deleteItem/{cartItemId}', [CartController::class,'deleteItem']);
});

Route::post('/checkCoupon', [CouponController::class,'checkCoupon']);

Route::prefix('order')->group(function(){
//Route::get('/getOrders', [NewOrderController::class,'getOrders']);
Route::get('/getProcessing', [NewOrderController::class,'getProcessing']);
Route::get('/getCompleted', [NewOrderController::class,'getCompleted']);
Route::get('/getRejected', [NewOrderController::class,'getRejected']);
Route::get('/getwaiting', [NewOrderController::class,'getwaiting']);
Route::get('/getDetails/{orderId}', [NewOrderController::class,'getDetails']);
Route::post('/add', [NewOrderController::class,'add']);
Route::post('/update/{orderId}', [NewOrderController::class,'update']);
Route::delete('/delete/{orderId}', [NewOrderController::class,'delete']);

Route::get('/deliveryAccept/{id}', [NewOrderController::class,'deliveryAccept']);
Route::get('/deliveryOnTheWay/{id}', [NewOrderController::class, 'deliveryOnTheWay']);
Route::get('/deliveryOnSite/{id}', [NewOrderController::class, 'deliveryOnSite']);
Route::get('/delivered/{id}', [NewOrderController::class, 'delivered']);
Route::get('/getDeliveryOrders', [NewOrderController::class,'getDeliveryOrders']);
// Route::get('/getDeliveryProcessing', [NewOrderController::class,'getDeliveryProcessing']);
// Route::get('/getDeliveryCompleted', [NewOrderController::class,'getDeliveryCompleted']);
// Route::get('/getDeliverywaiting', [NewOrderController::class,'getDeliverywaiting']);




Route::get('/markOrderReady/{orderId}', [NewPaymentController::class,'markOrderReady']);
});

Route::prefix('payment')->group(function(){

Route::post('/createPaymentIntent', [NewPaymentController::class,'createPaymentIntent']);
Route::post('/confirmOrderAfterPayment', [NewPaymentController::class, 'confirmOrderAfterPayment']);
// Route::post('/{order}/capture', [NewPaymentController::class, 'captureWhenReady']);
Route::post('/updatePaymentAmount/{orderId}', [NewPaymentController::class,'updatePaymentAmount']);
Route::post('/updatePaymentAndOrder/{orderId}', [NewPaymentController::class,'updatePaymentAndOrder']);
Route::post('/updateCard/{orderId}', [NewPaymentController::class,'updateCard']);

});

Route::post('/support', [SupportController::class, 'store']);

// Route::get('/test-notification', function() {
//     event(new SendNotification('اختبار', 'هذا إشعار تجريبي'));
//     return "تم إرسال الإشعار!";
// });

Route::get('/contact-info', [ContactInfoController::class, 'get']);


});


