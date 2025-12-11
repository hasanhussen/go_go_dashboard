<?php

namespace App\Http\Controllers\Api;

use App\Events\EditProduct;
use App\Http\Controllers\Controller;


use App\Http\Requests\StoreRequest;
use App\Http\Resources\StoreResource;
use App\Http\Requests\MealRequest;
use App\Models\Category;
use App\Models\Meal;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use App\Models\Store;
use App\Traits\HasImageUpload;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AdminNotification;

class MealController extends Controller
{

    use HasImageUpload;
    
public function getstoremeals($storeId)
{
    $store = Store::findOrFail($storeId);
    $meals = Meal::where('store_id',$storeId)->where('is_active', true)->where('status', '1')
            ->with('additionals','variants')->get();
    return response()->json($meals);

}

 public function mostSellingmeals($storeId)
{
    $store = Store::findOrFail($storeId);
    $meals = Meal::where('store_id',$storeId)->where('is_active', true)->where('status', '1')
            ->with('additionals','variants')->orderByDesc('points')->get();
    return response()->json($meals);

}

public function getwaitingmeals($storeId)
{
    $store = Store::findOrFail($storeId);
    $meals = Meal::where('store_id',$storeId)->where('status', '0')
            ->with('additionals','variants')->get();
    return response()->json($meals);

}

public function getBanedgmeals($storeId)
{
    $store = Store::findOrFail($storeId);
    $meals = Meal::where('store_id',$storeId)->where('status', '2')
            ->with('additionals','variants')->get();
    return response()->json($meals);

}


public function getMeal($meal_id)
{
    
    
    $meal = Meal::with('additionalsWithTrashed','variantsWithTrashed')->findOrFail($meal_id); 
  

    
    return response()->json($meal);
}


  public function addmeal(MealRequest $request){
    
    $validated =  $request->validated();
    $meal = $this->handleImageCreation($validated, Meal::class, 'products');
    if ($request->has('additionals')) {
        $meal->additionals()->attach($request->additionals);
    }

    if ($request->has('variants')) {
    foreach ($request->variants as $variantData) {
        $meal->variants()->create([
            'name' => $variantData['name'],
            'price' => $variantData['price'],
            'quantity' => $variantData['quantity'],
        ]);
    }
}

    return response()->json($meal);
 }

 public function editmeal(MealRequest $request,$meal_id){

    $validated = $request->validated();
    $meal = Meal::findOrFail($meal_id);
    if (!$request->has('quantity') || $request->quantity === null) {
    $validated['quantity'] = null;
}

    $updateMeal = $this->handleImageUpdate($validated, $meal , 'products');
    //  $meal->update($validated);
     if ($request->has('additionals')) {
        $updateMeal->additionals()->sync($request->additionals);
    }
    if ($request->has('variants')) {

    // امسح المقاسات القديمة
    $updateMeal->variants()->delete();

    // أضف الجديدة
    foreach ($request->variants as $variantData) {
        $updateMeal->variants()->create([
            'name' => $variantData['name'],
            'price' => $variantData['price'],
            'quantity' => $variantData['quantity'],
        ]);
    }
}



 $storeOwner = $updateMeal->store->user ?? null;
    broadcast(new EditProduct($updateMeal))->toOthers();
    $admin= User::role('admin')->orderBy('created_at', 'asc')->first();
       Notification::send($admin, new AdminNotification($storeOwner,type: 'product_edit',product: $updateMeal,store: $updateMeal->store)); 

   
return response()->json([
        'success' => 'تم تعديل المنتج بنجاح'
    ]);
 }



public function countTrashed($storeId){
   // المخفية
    $hiddenCount = Meal::where('store_id', $storeId)
        ->where('is_active', false)
        ->whereNull('deleted_at')
        ->count();

    // المحذوفة (soft deleted)
    $trashedCount = Meal::onlyTrashed()
        ->where('store_id', $storeId)
        ->count();

    // المجموع
    $totalTrashCount = $hiddenCount + $trashedCount;

        return response()->json([
        'trashCount' => $totalTrashCount,
    ]);
}


 public function hiddenMeals($store_id)
    {
        $meals = Meal::where('store_id', $store_id)
            ->where('is_active', false)
            ->get();

        return response()->json($meals);
    }

    // عرض الوجبات المحذوفة بانتظار موافقة الأدمن
    public function trashedMeals($store_id)
    {
        $meals = Meal::onlyTrashed()
            ->where('store_id', $store_id)
            ->get();

        return response()->json($meals);
    }

    // إخفاء منتج (بدل الحذف)
    public function hideMeal($meal_id)
    {
        $meal = Meal::findOrFail($meal_id);
        $meal->is_active = false;
        $meal->save();

        return response()->json(['success' => 'تم إخفاء المنتج']);
    }

    public function appeal ($meal_id, Request $request){
    $meal = Meal::withTrashed()->findOrFail($meal_id);
    $meal->appeal = $request->appeal_message;
    $meal->save();
    return response()->json(['message' => 'تم استلام طلب الاستئناف بنجاح. سيتم مراجعته في أقرب وقت ممكن.']);
}

    // استرجاع منتج مخفية
    public function restoreHiddenMeal($meal_id)
    {
        $meal = Meal::findOrFail($meal_id);
        $meal->is_active = true;
        $meal->save();

        return response()->json(['success' => 'تم إرجاع المنتج']);
    }

    // طلب حذف منتج (تروح على المحذوفات)
    public function softDeleteMeal($meal_id)
    {
        $meal = Meal::findOrFail($meal_id);
        $meal->delete();

        return response()->json(['success' => 'تم إرسال المنتج لسلة المحذوفات']);
    }

    // استرجاع منتج من المحذوفات (إذا رفض الأدمن الحذف)
    // public function restoreTrashedMeal($meal_id)
    // {
    //     $meal = Meal::withTrashed()->findOrFail($meal_id);
    //     $meal->restore();
    //     //$meal->is_pending_delete = false;
    //     $meal->is_active = false; // بترجع كمنتج مخفية
    //     $meal->save();

    //     return response()->json(['success' => 'تم استرجاع المنتج من المحذوفات']);
    // }

    // حذف نهائي (مخصص للأدمن فقط)
    public function forceDeleteMeal($meal_id)
    {
        $meal = Meal::withTrashed()->findOrFail($meal_id);
        $meal->forceDelete();

        return response()->json(['success' => 'تم حذف المنتج نهائياً']);
    }





}
