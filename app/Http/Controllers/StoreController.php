<?php

namespace App\Http\Controllers;


use App\Models\Store;
use App\Models\Category;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\StoreNotification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\HasImageUpload;
use Kreait\Firebase\Factory;

class StoreController extends Controller
{



 public function index(Request $request)
{
    $query = Store::with('user')->with('category');

    if ($request->has('search') && $request->search != '') {
        $search = $request->search;
        $query->where('name', 'LIKE', "%$search%")
              ->orWhere('address', 'LIKE', "%$search%")->orWhereHas('user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })->orWhereHas('category', function ($q) use ($search) {
                $q->where('type', 'LIKE', "%$search%");
            });
    }

    // ✅ فلترة حسب أكثر من حالة
    if ($request->filled('statuses')) {
        $statuses = $request->statuses; // array
        $query->whereIn('status', $statuses);
    }

       // ✅ فلترة حسب الصنف
    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }


    $stores = $query->paginate(10, ['*'], 'stores_page')->withQueryString();
    $trashedstores = Store::onlyTrashed()->with('user')->with('category')->paginate(10, ['*'], 'trashedstores_page')->withQueryString();
    // ✅ تجهيز الطلبات المرتبطة بالمتاجر المحذوفة (غير المنتهية)
    $pendingStoreOrders = [];

    foreach ($trashedstores as $store) {
        $pendingOrders = Cart::where('order_id', '!=', null)
            ->whereHas('order', function ($query) {
                $query->where('status', '!=', '4'); // الطلبات غير المنتهية
            })
            ->whereHas('meal', function ($query) use ($store) {
                $query->where('store_id', $store->id);
            })
            ->with('order:id,created_at,status')
            ->get()
            ->pluck('order')
            ->unique('id')
            ->values();

        if ($pendingOrders->isNotEmpty()) {
            $pendingStoreOrders[$store->id] = $pendingOrders;
        }
    }

    // ✅ جلب الفئات لخيارات الفلترة
    $categories = Category::all();

    return view('admin.stores.stores', compact('stores','trashedstores','categories','pendingStoreOrders'));
}



   public function show($id)
{
    $store = Store::withTrashed()->with(['meals', 'workingHours'])->findOrFail($id);
    //$store->workingHours = $store->workingHours->keyBy('day');
    $hideSearch = true;
    return view('admin.stores.stores_show', compact('store','hideSearch'));
}

public function accept(Request $request,$id)
{
    $store = Store::findOrFail($id);
    if ($store->updated_at->gt($request->input('last_seen_at'))) {
        return back()->with('warning', '⚠️ تم تعديل بيانات المتجر مؤخرًا. يرجى تحديث الصفحة قبل الموافقة.');
    }
    $store->status = '1'; // Active
        // تحديث التقييمات الافتراضية عند التفعيل
    $m = 50; // نفس القيمة المستخدمة في Bayesian
    $C = DB::table('ratings')->avg('rating') ?? 0;

    $store->total_ratings = 0;         // لا يوجد تقييمات بعد
    $store->avg_rating = round($C, 2); // متوسط التقييم العام
    $store->bayesian_score = round($C, 2);

    $store->save();

    $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
        $store->user,
        'تم قبول متجرك ✅',
        'تمت الموافقة على متجرك  ' . $store->name,
        [
        'type' => 'store_accepted',
        'store_id' => (string) $store->id, // لازم قيم الـ data تكون نصوص
       ]); 

  
    Notification::send($store->user, new StoreNotification($store, 'accept')); 

    return back()->with('success', 'تم قبول المتجر');
}

public function ban(Request $request, $id)
{
    $store = Store::findOrFail($id);
if ($store->updated_at->gt($request->input('last_seen_at'))) {
        return back()->with('warning', '⚠️ تم تعديل بيانات المتجر مؤخرًا. يرجى تحديث الصفحة قبل الرفض.');
    }
    
    if($store->status == '2'){ 
        // Unban
        $store->status = '1';
        $store->ban_reason = null;
        $store->ban_until = null;
        $store->save();

    $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
    $store->user,
        'تم الغاء حظر متجرك 🔓',
        'تم الغاء حظر متجرك و إتاحة زيارته ' . $store->name ,
        [
        'type' => 'store_unbanned',
        'store_id' => (string) $store->id, // لازم قيم الـ data تكون نصوص
    ]); 

    Notification::send($store->user, new StoreNotification($store, 'unbanned'));

        if($request->ajax()){
            return response()->json(['success' => true, 'message' => 'تم إلغاء الحظر']);
        }
        return back()->with('success', 'تم إلغاء الحظر');
    } else {
        // Ban
        if($request->ban_reason) {
            $days = (int)$request->input('ban_until'); 
            $store->ban_reason = $request->ban_reason;
            $store->ban_until = Carbon::now()->addDays($days); 
        } elseif($request->quick_reason) {
            $store->ban_reason = $request->quick_reason;
            $store->ban_until = $request->ban_until?? null;
        } else {
            $store->ban_reason = "لا يوجد سبب محدد";
            $store->ban_until = $request->ban_until?? null;
        }

        $store->status = '2';
        $store->ban_count += 1;

    $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
    $store->user,
        'تم حظر متجرك 🔒',
        'تم حظر متجرك  ' . $store->name . ' حتى '  . $store->ban_until . ' بسبب ' . $store->ban_reason,
        [
        'type' => 'store_banned',
        'store_id' => (string) $store->id, // لازم قيم الـ data تكون نصوص
        ]); 
 
    Notification::send($store->user, new StoreNotification($store, 'banned')); 
        $store->save();



        if($request->ajax()){
            return response()->json(['success' => true, 'message' => 'تم حظر المتجر']);
        }
        return back()->with('success', 'تم حظر المتجر');
    }
}



public function destroy(Request $request, $id)
{
    $store = Store::findOrFail($id);
     if ($store->updated_at->gt($request->input('last_seen_at'))) {
        return back()->with('warning', '⚠️ تم تعديل بيانات المتجر مؤخرًا. يرجى تحديث الصفحة قبل الرفض.');
    }

    // إذا اختر الادمن سبب سريع أو كتب سبب مخصص
    if($request->delete_reason) {
        $store->delete_reason = $request->delete_reason;
    } elseif($request->quick_reason) {
        $store->delete_reason = $request->quick_reason;
    } else {
        $store->delete_reason = "لا يوجد سبب محدد";
    }
    $store->deleted_by = Auth::user()->name;
    $store->save(); // حفظ السبب قبل الحذف
    
    $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
    $store->user,
        'تم رفض متجرك ❌',
        'تم رفض متجرك  ' . $store->name . ' بسبب '  . $store->delete_reason,
        [
        'type' => 'store_rejected',
        'store_id' => (string) $store->id, // لازم قيم الـ data تكون نصوص
    ]);

    
    Notification::send($store->user, new StoreNotification($store, 'reject')); 
    $store->delete();
    
    return back()->with('success', 'تم نقل المتجر إلى المحذوفات');
}


    // استرجاع متجر من المحذوفات (إذا رفض الأدمن الحذف)
    public function restoreTrashedstore($store_id)
    {
        $store = Store::withTrashed()->findOrFail($store_id);
        $store->restore();
        $store->delete_reason = null; // بترجع كمنتج مخفية
        $store->save();

        // 🔹 إرسال إشعار للمالك
    $storeOwner = $store->user ?? null;

    $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
    $storeOwner,
        'تم استرجاع متجرك ♻️',
        'تم استرجاع متجرك ' . $store->name . ' من المحذوفات',
        [
        'type' => 'store_restored',
        'store_id' => (string) $store->id,
        ]);

  
        Notification::send($storeOwner, new StoreNotification($store, 'restored'));
    
        return back()->with('success', 'تم استرجاع المتجر من المحذوفات');
    }

    // حذف نهائي (مخصص للأدمن فقط)
    public function forceDeletestore($store_id)
    {
        $store = Store::onlyTrashed()->findOrFail($store_id);
        
         $exsist =  Cart::where('order_id','!=', null)->whereHas('order', function ($query) use ($store) {
                    $query->where('status','!=','4');
                })
                ->whereHas('meal', function ($query) use ($store) {
                    $query->where('store_id', $store->id);
                })->count();
            if($exsist > 0){
                return back()->with('warning', 'لا يمكنك حذف المنتج لانه موجود في طلبات قيد المعالجة'); // تخطي هذا المتجر لأنه لديه طلبات قيد المعالجة
            }

            if ($store->image) Storage::disk('public')->delete($store->image);
    if ($store->cover) Storage::disk('public')->delete($store->cover);
        $store->forceDelete();

        return back()->with('success', 'تم حذف المتجر نهائياً');
    }

    public function emptyTrash (){
        $trashedStores = Store::onlyTrashed()->get();

        foreach ($trashedStores as $store) {
          $exsist =  Cart::where('order_id','!=', null)->whereHas('order', function ($query) use ($store) {
                    $query->where('status','!=','4');
                })
                ->whereHas('meal', function ($query) use ($store) {
                    $query->where('store_id', $store->id);
                })->count();
            if($exsist > 0){
                continue; // تخطي هذا المتجر لأنه لديه طلبات قيد المعالجة
            }
                if ($store->image) Storage::disk('public')->delete($store->image);
    if ($store->cover) Storage::disk('public')->delete($store->cover);
            $store->forceDelete();
        }

        return back()->with('success', 'تم إفراغ سلة المحذوفات بنجاح');
    }


}
