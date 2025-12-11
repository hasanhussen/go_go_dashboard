<?php

namespace App\Http\Controllers\Api;

use App\Events\EditStore;
use App\Http\Controllers\Controller;

use App\Http\Requests\StoreRequest;
use App\Http\Resources\StoreResource;
use App\Http\Requests\MealRequest;
use App\Models\Category;
use App\Models\Meal;
use App\Models\Store;
use App\Models\User;
use App\Traits\HasImageUpload;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AdminNotification;


class StoreController extends Controller
{

    use HasImageUpload;

    public function myStores() {
    $user_id = Auth::user()->id;


    $activeStoresData = Store::where('user_id', $user_id)
        ->where('status','1')
        ->with('user')->with('workingHours')
        ->get();

    $bannedStoresData = Store::where('user_id', $user_id)
        ->where('status', '2')
        ->with('user')->with('workingHours')
        ->get();

    $NewStoreData = Store::where('user_id', $user_id)->where('status','0')->with('user')->with('workingHours')->get();
    
    $DeletedStoresData = Store::withTrashed()->where('user_id', $user_id)->where('deleted_at', '!=', null)->with('user')->with('workingHours')->get();
    

    $activeStores = StoreResource::collection($activeStoresData);
    $bannedStores = StoreResource::collection($bannedStoresData);
    $NewStores= StoreResource::collection($NewStoreData);
    $DeletedStores= StoreResource::collection($DeletedStoresData);

    return response()->json([
        'active_stores' => $activeStores,
        'banned_stores' => $bannedStores,
        'new_stores' => $NewStores,
        'deleted_stores' => $DeletedStores
    ]);
    }
// public function myActiveStores() {
//     $user_id = Auth::user()->id;

//     $storeData = Store::where('user_id', $user_id)
//         ->where('status','1')
//         ->with('user')
//         ->get();

//     $store = StoreResource::collection($storeData);
//     return response()->json($store);
// }

// public function myBannedStores() {
//     $user_id = Auth::user()->id;

//     $storeData = Store::where('user_id', $user_id)
//         ->where('status', '2')
//         ->with('user')
//         ->get();

//     $store = StoreResource::collection($storeData);
//     return response()->json($store);
// }


//    public function myNewStores(){
//     $user_id = Auth::user()->id;
//     $storeData = Store::where('user_id', $user_id)->where('status','0')->with('user')->get();
//     $store= StoreResource::collection($storeData);
//     return response()->json($store);
//  }

//     public function myDeletedStores(){
//     $user_id = Auth::user()->id;
//     $storeData = Store::withTrashed()->where('user_id', $user_id)->where('deleted_at', '!=', null)->with('user')->get();
//     $store= StoreResource::collection($storeData);
//     return response()->json($store);
//  }


   public function profilestore($store_id){
    $storeData = Store::findOrFail($store_id);
    $storeData->load('workingHours');
    $storeData->load('user');
    $store= new StoreResource($storeData);
    return response()->json($store);
 }


  public function addStore(StoreRequest $request){
    $validated =  $request->validated();
    $user_id = Auth::user()->id;
    $validated['user_id'] = $user_id;
    // استدعاء التريت
   $storeData = $this->handleImageCreation($validated, Store::class, 'stores');
       // 2. حفظ أوقات الدوام
    if ($request->has('working_hours')) {
        foreach ($request->working_hours as $item) {
            $storeData->workingHours()->create([
                'day'      => $item['day'],
                'open_at'  => $item['open_at'] ?? null,
                'close_at' => $item['close_at'] ?? null,
                // 'is_open'  => $item['is_open'] ?? true,
                // 'is_24'    => $item['is_24'] ?? false,
            ]);
        }
    }
    // $storeData = Store::create($validated);
    $storeData->load('user');
    $storeData->load('workingHours');
    $store= new StoreResource($storeData);
    return response()->json($store);
 }

  public function deletestore($store_id){
    try {
            $store = Store::findOrFail($store_id);
            $store->deleted_by = Auth::user()->name;
            $store->delete();
            return response()->json('Store Deleted successfully', 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'store not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong while deleting the store'], 500);
        }
 }


public function editStore(StoreRequest $request, $store_id)
{
    $validated =  $request->validated();
    $user_id = Auth::user()->id;
    $validated['user_id'] = $user_id;
    $store = Store::find($store_id);
    if (!$store) {
        return response()->json(['error' => 'المتجر غير موجود'], 404);
    }

    if ($store->user_id != $user_id) {
    return response()->json(['error' => 'غير مصرح بالتعديل على هذا المتجر'], 403);
}

    // $store->update($validated);
   $updateStore = $this->handleImageUpdate($validated, $store , 'stores');
   if ($request->has('working_hours')) {

    foreach ($request->working_hours as $item) {

        $day = $item['day'];

        // ندور على سجل اليوم إذا موجود
        $existing = $updateStore->workingHours()
            ->where('day', $day)
            ->first();

        if ($existing) {
            // تحديث اليوم الموجود
            $existing->update([
                'open_at'  => $item['open_at'] ?? null,
                'close_at' => $item['close_at'] ?? null,
                // 'is_open'  => $item['is_open'] ?? true,
                // 'is_24'    => $item['is_24'] ?? false,
            ]);
        } else {
            // إنشاء سجل جديد لليوم إذا غير موجود
            $updateStore->workingHours()->create([
                'day'      => $day,
                'open_at'  => $item['open_at'] ?? null,
                'close_at' => $item['close_at'] ?? null,
                // 'is_open'  => $item['is_open'] ?? true,
                // 'is_24'    => $item['is_24'] ?? false,
            ]);
        }
    }
}
    $updateStore->load('user'); 
    $updateStore->load('workingHours');

    broadcast(new EditStore($updateStore))->toOthers();
    // $admins = User::role(['admin', 'editor'])->get();
    $admin= User::role('admin')->orderBy('created_at', 'asc')->first();
       Notification::send($admin, new AdminNotification($updateStore->user,type: 'store_edit',store: $updateStore)); 

    return response()->json(new StoreResource($updateStore));
}


 public function getCategoryStores($category_id)
{
    // التحقق من وجود الصنف قبل جلب المتاجر
    if (!Category::where('id', $category_id)->exists()) {
        return response()->json(['error' => 'الصنف غير موجود'], 404);
    }

    $storeData = Store::where('category_id', $category_id)->where('status','1')->with('user')->with('workingHours')->orderByDesc('bayesian_score')->get();
    $store= StoreResource::collection($storeData);
    
    return response()->json($store);
}

  public function follow($storeId)
{
    $user_id = Auth::user()->id;
    $user= User::findOrFail($user_id);
    $store = Store::findOrFail($storeId);

    // التحقق من عدم المتابعة مسبقاً
    if (!$user->followStore()->where('store_id', $storeId)->exists()) {
        $user->followStore()->attach($storeId);
        $store->increment('followers');
    }

    return response()->json(['followers' => $store->followers]);
}

public function unfollow($storeId)
{
    $user_id = Auth::user()->id;
    $user= User::findOrFail($user_id);
    $store = Store::findOrFail($storeId);

    // التحقق من المتابعة مسبقاً
    if ($user->followStore()->where('store_id', $storeId)->exists()) {
        $user->followStore()->detach($storeId);
        $store->decrement('followers');
    }

    return response()->json(['followers' => $store->followers]);
}

  public function checkfollow($storeId)
{
    $user_id = Auth::user()->id;
    $user= User::findOrFail($user_id);
    $store = Store::findOrFail($storeId);

    // التحقق من عدم المتابعة مسبقاً
    if (!$user->followStore()->where('store_id', $storeId)->exists()) {
        
        return response()->json(['follow' => 'no']);
    }

    return response()->json(['follow' => 'yes']);

    
}

public function followedStores(){
    $user_id = Auth::user()->id;
    $user= User::findOrFail($user_id);
    $stores = $user->followStore()->where('status','1')->with('user')->get();
    $store= StoreResource::collection($stores);
    return response()->json($store);
}

public function appeal ($store_id, Request $request){
    $store = Store::withTrashed()->findOrFail($store_id);
    $store->appeal = $request->appeal_message;
    $store->save();
    return response()->json(['message' => 'تم استلام طلب الاستئناف بنجاح. سيتم مراجعته في أقرب وقت ممكن.']);
}

}
