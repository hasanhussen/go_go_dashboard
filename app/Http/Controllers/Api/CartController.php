<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Http\Requests\CartRequest;
use App\Models\Additional;
use App\Models\Cart;
use App\Models\Meal;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CartController extends Controller
{
    
public function getItems()
{
    $user = Auth::user();
    $oneDaysAgo = Carbon::now('UTC')->subHours(24);

    $cartItems = Cart::with([
    'variant',   // جميع المقاسات مع المحذوفة
    'additionalItems',
    'order',
    'meal.store'
])
        ->where('user_id', $user->id)->whereNull('order_id')
        ->where('created_at', '>=', $oneDaysAgo)
        ->get();
        
        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'السلة الشرائية فارغة'], 200);
        }

     


    return response()->json($cartItems);
}


    public function addItem(CartRequest $request,$orderId){
    $userId = Auth::id();
    if (!$userId) {
        return response()->json(['error' => 'غير مصدق'], 401);
    }
    

    // Normalize orderId to integer
    $orderId = (int) $orderId;

    // Prepare validated data
    $validated = $request->validated();
    $validated['user_id'] = $userId;
    if ($orderId !== 0) {
        $validated['order_id'] = $orderId;
    }

    // تحقق من وجود العنصر بحسب الهدف (open cart أو طلب محدد)
    $query = Cart::where('user_id', $userId)
                 ->where('meal_id', $request->meal_id)->when($request->variant_id !== null, function($q) use ($request) {
        $q->where('variant_id', $request->variant_id);
    });

    if ($orderId === 0) {
        $query->whereNull('order_id'); // السلة المفتوحة
    } else {
        $query->where('order_id', $orderId); // داخل طلب محدد
    }

    if ($orderId !== 0) {
        $order = Order::findOrFail($orderId);
        if ($order->status != '0') {
            return response()->json([
                'error' => 'لا يمكنك التعديل على الطلب بعد الموافقة عليه'
            ], 403);
        }
    }


    if ($query->exists()) {
        $msg = $orderId === 0 ? 'هذه المنتج موجودة بالفعل في السلة' : 'هذه المنتج موجودة بالفعل في طلبك';
        return response()->json(['error' => $msg], 400);
    }

    
if($orderId === 0)
{ 
       $cartItem  = Cart::create($validated);

//            if ($request->filled('variants') && is_array($request->variants)) {

//     $syncVariantsData = [];

//     foreach ($request->variants as $variant) {
//         $syncVariantsData[$variant['id']] = [
//             'quantity' => $variant['quantity'],
//         ];
//     }

//     $cartItem->variants()->sync($syncVariantsData);
// }

    if ($request->filled('additionals') && is_array($request->additionals)) {

    $syncData = [];

    foreach ($request->additionals as $additional) {
        $syncData[$additional['id']] = [
            'quantity' => $additional['quantity'],
            'old_additional_price'  => $additional['old_additional_price'] ?? null,
        ];
    }

    $cartItem->additionalItems()->sync($syncData);
}

    return response()->json([
        'success'=>'تم اضافة العنصر إلى السلة الشرائية'
    ]);
}else{

        return response()->json([
            'success'=>'تمت إضافة العنصر إلى طلبك بنجاح'
        ],200);}


    }

    public function deleteItem($cartItemId){
        
    $cartItem = Cart::findOrFail($cartItemId);
    $cartItem->additionalItems()->detach(); 
    if($cartItem->order_id){
        $order = Order::findOrFail($cartItem->order_id);
        if($order->status !='0'){
            return response()->json([
                'error' => 'لا يمكنك حذف العنصر من الطلب بعد الموافقة عليه'
            ], 403);
        } else {
           if ($cartItem->meal->quantity !== null) {
        $cartItem->meal->increment('quantity', $cartItem->quantity);
    }
    // if (!empty($cartItem->variants)) {
    //     foreach ($cartItem->variants as $variant) {
    //         if ($variant->quantity !== null) {
    //             $variant->increment('quantity', $cartItem->variants()->where('meal_variants', $variant->id)->first()->pivot->quantity);
    //         }
    //     }
    // }
    if (!empty($cartItem->additionalItems)) {
        foreach ($cartItem->additionalItems as $additional) {
            if ($additional->quantity !== null) {
                $additional->increment('quantity', $cartItem->additionalItems()->where('additional_id', $additional->id)->first()->pivot->quantity);
            }
        }
    }
        }
    }
$cartItem->delete();


    return response()->json([
        'success'=>'تم حذف العنصر من السلة الشرائية'
    ]);
    }

public function updateItem(CartRequest $request, $cartItemId)
{
    $validated = $request->validated();
    $user_id = Auth::user()->id;
    $validated['user_id'] = $user_id;

    $cartItem = Cart::findOrFail($cartItemId);
    $cartItem->update($validated);


    // if ($request->has('variants')) {
    //     $syncVariantsData = [];

    //     foreach ($request->variants as $variant) {
    //     $syncVariantsData[$variant['id']] = [
    //         'quantity' => $variant['quantity'],
    //     ];
    // }
    //     $cartItem->variants()->sync($syncVariantsData);
    // }


    if ($request->has('additionals')) {
        $syncData = [];

        foreach ($request->additionals as $additional) {
        $syncData[$additional['id']] = [
            'quantity' => $additional['quantity'],
            'old_additional_price'  => $additional['old_additional_price'] ?? null,
        ];
    }
        $cartItem->additionalItems()->sync($syncData);
    }

    return response()->json([
        'success' => 'تم إجراء التعديلات بنجاح'
    ]);
}


// public function updateOrderItem(CartRequest $request, $cartItemId)
// {
//     $validated = $request->validated();
//     $user_id = Auth::user()->id;
//     $validated['user_id'] = $user_id;

//     $cartItem = Cart::findOrFail($cartItemId);
//     $cartItem->update($validated);


//     if ($request->has('additionals')) {
//         $syncData = [];

//         foreach ($request->additionals as $additional) {
//         $syncData[$additional['id']] = [
//             'quantity' => $additional['quantity'],
//             'old_additional_price'  => $additional['old_additional_price'] ?? null,
//         ];
//     }

//         $cartItem->additionalItems()->sync($syncData);
//     }

//     return response()->json([
//         'success' => 'تم إجراء التعديلات بنجاح'
//     ]);
// }



}
