<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Notification;
use App\Notifications\MealNotification;

class MealController extends Controller
{
    


 public function index(Request $request)
{
    $query = Meal::with('store');

    if ($request->has('search') && $request->search != '') {
        $search = $request->search;
        $query->where('name', 'LIKE', "%$search%")
              ->orWhereHas('store', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            });
    }

       // ✅ فلترة حسب أكثر من حالة
    if ($request->filled('statuses')) {
        $statuses = $request->statuses; // array
        $query->whereIn('status', $statuses);
    }

    $products = $query->paginate(10, ['*'], 'products_page')->withQueryString();
    $trashedproducts = Meal::onlyTrashed()->with('store')->paginate(10, ['*'], 'trashedproducts_page')->withQueryString();
    $pendingProductOrders = [];

    foreach ($trashedproducts as $product) {
        $pendingOrders = Cart::where('meal_id',$product->id)->where('order_id','!=', null)->whereHas('order', function ($query) use ($product) {
                    $query->where('status','!=','4');
                })
            ->with('order:id,created_at,status')
            ->get()
            ->pluck('order')
            ->unique('id')
            ->values();

        if ($pendingOrders->isNotEmpty()) {
            $pendingProductOrders[$product->id] = $pendingOrders;
        }
    }

    return view('admin.products.products', compact('products','trashedproducts','pendingProductOrders'));
}




public function show($product_id)
{
    $product = Meal::with('additionals','store','variants')->findOrFail($product_id);
    $hideSearch = true;
    return view('admin.products.products_show', compact('product','hideSearch'));
}

public function accept(Request $request,$id)
{
    $meal = Meal::findOrFail($id);
    if ($meal->updated_at->gt($request->input('last_seen_at'))) {
        return back()->with('warning', '⚠️ تم تعديل بيانات المتجر مؤخرًا. يرجى تحديث الصفحة قبل الموافقة.');
    }
    $meal->status = '1'; // Active
    $meal->save();

        // 🔹 الوصول إلى صاحب المتجر
    $storeOwner = $meal->store->user ?? null;
    $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
        $storeOwner,
        'تم قبول المنتج ✅',
        "تمت الموافقة على منتجك {$meal->name}",
        [
        'type' => 'meal_accepted',
        'store_id' => (string) $meal->store->id,  // لازم قيم الـ data تكون نصوص
        ]);

    Notification::send($storeOwner, new MealNotification($meal, 'accept'));
   

    return back()->with('success', 'تم قبول المنتج');

}

public function ban(Request $request,$id)
{
    $meal = Meal::findOrFail($id);
        if ($meal->updated_at->gt($request->input('last_seen_at'))) {
        return back()->with('warning', '⚠️ تم تعديل بيانات المتجر مؤخرًا. يرجى تحديث الصفحة قبل الموافقة.');
    }
    if($meal->status == '2'){ 
        // Unban
        $meal->status = '1';
        $meal->ban_reason = null;
        $meal->ban_until = null;
        $meal->save();

$storeOwner = $meal->store->user ?? null;

    $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
        $storeOwner,
        'تم إالغاء حظر منتجك 🔓',
        'تم إالغاء حظر منتجك  ' . $meal->name,
        [
        'type' => 'meal_unbanned',
        'store_id' => (string) $meal->store->id,
        ]);

    Notification::send($storeOwner, new MealNotification($meal, 'unbanned')); 
        if($request->ajax()){
            return response()->json(['success' => true, 'message' => 'تم إلغاء الحظر']);
        }
        return back()->with('success', 'تم إلغاء الحظر');
    } else {
        // Ban
        if($request->ban_reason) {
            $days = (int)$request->input('ban_until'); 
            $meal->ban_reason = $request->ban_reason;
            $meal->ban_until = Carbon::now()->addDays($days); 
        } elseif($request->quick_reason) {
            $meal->ban_reason = $request->quick_reason;
            $meal->ban_until = $request->ban_until?? null;
        } else {
            $meal->ban_reason = "لا يوجد سبب محدد";
            $meal->ban_until = $request->ban_until?? null;
        }

        $meal->status = '2';
        $meal->ban_count += 1;
        $meal->save();

        $storeOwner = $meal->store->user ?? null;

        $notificationService = new \App\Services\NotificationService();

        $notificationService->sendToUser(
        $storeOwner,
        'تم حظر منتجك 🔒',
        'تم حظر منتجك  ' . $meal->name . ' حتى '  . $meal->ban_until . ' بسبب ' . $meal->ban_reason,
        [
        'type' => 'meal_banned',
        'store_id' => (string) $meal->store->id,  // لازم قيم الـ data تكون نصوص
       ]);


    Notification::send($storeOwner, new MealNotification($meal, 'banned')); 

        if($request->ajax()){
            return response()->json(['success' => true, 'message' => 'تم حظر المتجر']);
        }
        return back()->with('success', 'تم حظر المتجر');
    }
}

public function destroy(Request $request,$id)
{
    $meal = Meal::findOrFail($id);
    if ($meal->updated_at->gt($request->input('last_seen_at'))) {
        return back()->with('warning', '⚠️ تم تعديل بيانات المتجر مؤخرًا. يرجى تحديث الصفحة قبل الرفض.');
    }
        // إذا اختر الادمن سبب سريع أو كتب سبب مخصص
    if($request->delete_reason) {
        $meal->delete_reason = $request->delete_reason;
    } elseif($request->quick_reason) {
        $meal->delete_reason = $request->quick_reason;
    } else {
        $meal->delete_reason = "لا يوجد سبب محدد";
    }

    $meal->save(); // حفظ السبب قبل الحذف


    
        // 🔹 الوصول إلى صاحب المتجر
    $storeOwner = $meal->store->user ?? null;
    $notificationService = new \App\Services\NotificationService();

        $notificationService->sendToUser(
        $storeOwner,
        'تم رفض منتجك ❌',
        'تم رفض منتجك  ' . $meal->name . ' بسبب '  . $meal->delete_reason,
        [
        'type' => 'meal_rejected',
        'store_id' => (string) $meal->store->id,  // لازم قيم الـ data تكون نصوص
        ]);


        Notification::send($storeOwner, new MealNotification($meal, 'reject'));
        $meal->delete();
        return back()->with('success', 'تم نقل المنتج إلى المحذوفات');
}


    // استرجاع منتج من المحذوفات (إذا رفض الأدمن الحذف)
    public function restoreTrashedMeal($meal_id)
    {
        $meal = Meal::withTrashed()->findOrFail($meal_id);
        $meal->restore();
        $meal->delete_reason = null; // بترجع كمنتج مخفية
        $meal->save();

        $storeOwner = $meal->store->user ?? null;

        $notificationService = new \App\Services\NotificationService();

        $notificationService->sendToUser(
        $storeOwner,
        'تم استرجاع المنتج ♻️',
        "تم استرجاع منتجك {$meal->name} من المحذوفات",
        [
        'type' => 'meal_restored',
        'store_id' => (string) $meal->store->id,   // لازم قيم الـ data تكون نصوص
        ]);

        // 🔔 إرسال إشعار Laravel Notification
        Notification::send($storeOwner, new MealNotification($meal, 'restored'));
    
        return back()->with('success', 'تم استرجاع المنتج من المحذوفات');
    }

    // حذف نهائي (مخصص للأدمن فقط)
    public function forceDeleteMeal($meal_id)
    {
        $meal = Meal::onlyTrashed()->findOrFail($meal_id);
        $exsist =  Cart::where('meal_id',$meal->id)->where('order_id','!=', null)->whereHas('order', function ($query) use ($meal) {
                    $query->where('status','!=','4');
                })->count();
            if($exsist > 0){
                return back()->with('warning', 'لا يمكنك حذف المنتج لانه موجود في طلبات قيد المعالجة'); // تخطي هذا المتجر لأنه لديه طلبات قيد المعالجة
            }
            if ($meal->image) {
                // حذف الصورة من التخزين
                Storage::disk('public')->delete($meal->image);
            }
        $meal->forceDelete();

        return back()->with('success', 'تم حذف المنتج نهائياً');
    }

        public function emptyTrash (){
        $trashedproducts = Meal::onlyTrashed()->get();

        foreach ($trashedproducts as $product) {
         $exsist =  Cart::where('meal_id',$product->id)->where('order_id','!=', null)->whereHas('order', function ($query) use ($product) {
                    $query->where('status','!=','4');
                })->count();
            if($exsist > 0){
                continue; // تخطي هذا المتجر لأنه لديه طلبات قيد المعالجة
            }
            if ($product->image) {
                // حذف الصورة من التخزين
                Storage::disk('public')->delete($product->image);
            }
            $product->forceDelete();
        }

        return back()->with('success', 'تم إفراغ سلة المحذوفات بنجاح');
    }

}
