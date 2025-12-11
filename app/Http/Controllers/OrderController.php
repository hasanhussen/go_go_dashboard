<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\OrderLog;
use App\Models\Coupon;
use App\Notifications\OrderNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Target;

class OrderController extends Controller
{
    


public function index(Request $request)
{
    // الأساس: query مع اليوزر
    $baseQuery = Order::with('user')->with('delivery');

    // 🔍 البحث → يطبق عالكل
    if ($request->filled('search')) {
        $search = $request->search;

        if (is_numeric($search)) {
            $baseQuery->where('id', $search);
        } else {
            $baseQuery->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            });
        }
    }

    // —— pendingOrders: عليها فلترة إضافية ——
    $pendingQuery = (clone $baseQuery)->whereIn('status', ['0','1','2','3','5']);

    if ($request->filled('payment_method')) {
        $pendingQuery->whereIn('payment_method', (array) $request->payment_method);
    }

    if ($request->filled('statuses')) {
        $pendingQuery->whereIn('status', (array) $request->statuses);
    }

      $pendingQuery->orderByRaw("FIELD(status, 0,1,2,3,5)") // ترتيب حسب الحالة
                 ->orderBy('user_id')                 // ترتيب حسب المستخدم
                 ->orderBy('created_at');  

    $completedQuery = (clone $baseQuery)->where('status', '4');

    // paginate مع أسماء صفحات مختلفة
    $pendingOrders   = $pendingQuery->paginate(10, ['*'], 'pending_page')->withQueryString();
    $completedOrders = $completedQuery->paginate(10, ['*'], 'completed_page')->withQueryString();
    $rejectedOrders = Order::onlyTrashed()->with('user')->with('delivery')->paginate(10, ['*'], 'rejected_page')->withQueryString();

    $deliveryMen = User::role('delivery')
    ->with(['activeOrders' => function ($query) {
        $query->whereIn('status', ['1', '2', '3']); // الطلبات النشطة فقط
    }])
    ->get();



    return view('admin.orders.orders', compact('pendingOrders','completedOrders','rejectedOrders','deliveryMen'));
}

   public function show($id)
{
    $order = Order::with([
        'carts.meal' => function ($query) {
            // تضم الوجبات المحذوفة أو المخفية
            $query->withTrashed(); // هذا يخلي softDeleted تظهر
        },
        'carts.additionalItems' => function ($query) {
            $query->withTrashed(); // إذا الإضافات فيها softDeletes
        },
        'coupon',
        'carts.meal.store',
        'logs.admin', 
    ])->findOrFail($id);

    $hideSearch = true;

    return view('admin.orders.orders_show', compact('order','hideSearch'));
}


public function assignDelivery(Request $request, $id)
{
    $order = Order::findOrFail($id);

    // تحقق أن المستخدم المختار هو عامل توصيل
    if ($request->delivery_id) {
        $delivery =User::where('id', $request->delivery_id)
            ->role('delivery')
            ->first();

        if (!$delivery) {
            return back()->with('error', 'المستخدم المختار ليس عامل توصيل ❌');
        }

          if ($order->status == '4') {
            return back()->with('error', 'لا يمكن تغيير عامل التوصيل لطلب تم تسليمه ❌');
        }

        if($order->status == '5')
            {
                $notificationService = new \App\Services\NotificationService();
                    $notificationService->sendToUser(
        $order->user,
        'تم قبول طلبك ✅',
        'تمت الموافقة على طلبك رقم ' . $order->id,
        [
        'type' => 'order_accepted',
        'order_id' => (string) $order->id, // لازم قيم الـ data تكون نصوص
       ]);   


    Notification::send($order->user, new OrderNotification($order, 'accept'));

    return back()->with('success', 'تم قبول الطلب');
        }

        if($order->status != '0'){

    $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
        $delivery,
        'هناك مهمة لك 🛎️',
        'تم إسناد اليك الطلب رقم ' . $order->id,
        [
        'type' => 'order_assign',
        'order_id' => (string) $order->id, // لازم قيم الـ data تكون نصوص
        ]);
            
     
    Notification::send($delivery, new OrderNotification($order, 'order_assign'));
           
        }

        $order->delivery_id = $delivery->id;
    } else {
        // إذا اختار "غير محدد"
        if($order->status != '0' && $order->status != '4'){
            $firebase = (new Factory)
    ->withServiceAccount(config('services.firebase.credentials'))
    ->createMessaging();

$message = CloudMessage::new()
    ->withNotification([
        'title' => 'طلب جديد 🛎️',
        'body' => ' هناك طلب بانتظارالاستلام',
    ])
    ->withData(['type' => 'new_order'])
    ->toTopic('delivery');

$firebase->send($message);
Notification::send($order->user, new OrderNotification($order, 'new_order'));

        }
        $order->delivery_id = null;
    }

    $order->save();

    return back()->with('success', 'تم تحديث عامل التوصيل بنجاح ✅');
}



public function accept(Request $request, $id)
{
    $order = Order::findOrFail($id);


if ($order->is_editing) {
        return back()->with('warning', '⚠️ يتم تعديل بيانات الطلب الآن. يرجى الانتظار لبضع دقائق قبل الموافقة.');
    }
if ($order->updated_at->gt($request->input('last_seen_at'))) {
        return back()->with('warning', '⚠️ تم تعديل بيانات الطلب مؤخرًا. يرجى تحديث الصفحة قبل الموافقة.');
    }

    if (!$order->delivery_id) {
        $firebase = (new Factory)
    ->withServiceAccount(config('services.firebase.credentials'))
    ->createMessaging();

$message = CloudMessage::new()
    ->withNotification([
        'title' => 'طلب جديد 🛎️',
        'body' => ' هناك طلب بانتظارالاستلام',
    ])
    ->withData(['type' => 'new_order'])
    ->toTopic('delivery');

$firebase->send($message);
Notification::send($order->user, new OrderNotification($order, 'new_order'));

$order->status = '5';
    $order->save();
return back()->with('success', 'تم إرسال الطلب إلى عمال التوصيل بنجاح ✅');
    }

    $order->status = '1'; // Active
    $order->save();
    $delivery =User::where('id',$order->delivery_id)
            ->role('delivery')
            ->first();

    $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
        $delivery,
        'هناك مهمة لك 🛎️',
        'تم إسناد اليك الطلب رقم ' . $order->id,
        [
        'type' => 'order_assign',
        'order_id' => (string) $order->id, // لازم قيم الـ data تكون نصوص
        ]);        

    Notification::send($delivery, new OrderNotification($order, 'order_assign'));

        $notificationService->sendToUser(
        $order->user,
        'تم قبول طلبك ✅',
        'تمت الموافقة على طلبك رقم ' . $order->id,
        [
        'type' => 'order_accepted',
        'order_id' => (string) $order->id, // لازم قيم الـ data تكون نصوص
       ]);   


    Notification::send($order->user, new OrderNotification($order, 'accept'));

    return back()->with('success', 'تم قبول الطلب');

}


public function destroy(Request $request,$id)
{
   $order = Order::findOrFail($id);

   if ($order->is_editing) {
        return back()->with('warning', '⚠️ يتم تعديل بيانات الطلب الآن. يرجى الانتظار لبضع دقائق قبل الرفض.');
    }
      if ($order->updated_at->gt($request->input('last_seen_at'))) {
        return back()->with('warning', '⚠️ تم تعديل بيانات الطلب مؤخرًا. يرجى تحديث الصفحة قبل الرفض.');
    }
        // إذا اختر الادمن سبب سريع أو كتب سبب مخصص
    if($request->delete_reason) {
        $order->delete_reason = $request->delete_reason;
    } elseif($request->quick_reason) {
        $order->delete_reason = $request->quick_reason;
    } else {
        $order->delete_reason = "لا يوجد سبب محدد";
    }

    // تحقق إذا الطلب مرتبط بكوبون
        if ($order->coupon_id) {
            $coupon = Coupon::find($order->coupon_id);
            if ($coupon) {
                $coupon->count += 1; // زيادة العدد بمقدار 1
                $coupon->save();
            }
        }
    
            // استرجاع الكميات للوجبات والإضافات
            foreach ($order->carts as $cartItem) {
                $meal = $cartItem->meal;
                if ($meal && $meal->quantity !== null) {
                    $meal->increment('quantity', $cartItem->quantity);
                }

                foreach ($cartItem->additionalItems as $additional) {
                    if ($additional && $additional->quantity !== null) {
                        $additional->increment('quantity', $cartItem->pivot->quantity);
                        
                    }
                }
            }

        if($order->payment_method == 'card' && !$order->is_paid){
      $payment = $order->payment;
    if (!$payment || $payment->status !== 'requires_capture') {
        return response()->json(['error' => 'لا يوجد دفع محجوز للسحب.'], 400);
    }

    Stripe::setApiKey(config('services.stripe.secret'));

    $intent = \Stripe\PaymentIntent::retrieve($payment->payment_intent_id);
    $intent->cancel();
        }

    $order->save(); // حفظ السبب قبل الحذف
    $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
        $order->user,
        'تم رفض طلبك ❌',
         'تم رفض طلبك رقم ' . $order->id . ' بسبب ' .$order->delete_reason,
        [
        'type' => 'order_rejected',
        'order_id' => (string) $order->id, // لازم قيم الـ data تكون نصوص
        ]);        

    Notification::send($order->user, new OrderNotification($order, 'reject'));

    $order->delete();
    return back()->with('success', 'تم نقل الطلب إلى المحذوفات');
}


    // حذف نهائي (مخصص للأدمن فقط)
    public function forceDeleteOrder($order_id)
    {
        $order = Order::onlyTrashed()->findOrFail($order_id);
    if($order->payment_method == 'card' && !$order->is_paid){
      $payment = $order->payment;
    if (!$payment || $payment->status !== 'requires_capture') {
        return response()->json(['error' => 'لا يوجد دفع محجوز للسحب.'], 400);
    }

    Stripe::setApiKey(config('services.stripe.secret'));

    $intent = \Stripe\PaymentIntent::retrieve($payment->payment_intent_id);
    $intent->cancel();
        }
        $order->forceDelete();

        return back()->with('success', 'تم حذف الطلب نهائياً');
    }


        public function emptyTrash (){
        $trashedOrders = Order::onlyTrashed()->get();

        foreach ($trashedOrders as $order) {
            $order->forceDelete();
        }

        return back()->with('success', 'تم إفراغ سلة المحذوفات بنجاح');
    }
    




    public function forceStatusChange($id)
{
    $order = Order::findOrFail($id);
    $oldStatus = $order->status;

    $firebase = (new Factory)
        ->withServiceAccount(config('services.firebase.credentials'))
        ->createMessaging();

    // تحديد الحالة التالية حسب التسلسل
    switch ($order->status) {
        case '1': // تم الموافقة من الإدارة
            $order->status = '2'; // في الطريق
            $title = 'طلبك قيد التوصيل 🛵';
            $body = 'طلبك رقم ' . $order->id . ' قيد التوصيل';
            $type = 'order_on_the_way';
            break;

        case '2': // في الطريق
            $order->status = '3'; // في الموقع
            $title = 'عامل التوصيل في الموقع';
            $body = 'يرجى استلام طلبك رقم ' . $order->id;
            $type = 'order_on_site';
            break;

        case '3': // في الموقع
            // في حال الدفع بالبطاقة، نحجز المبلغ قبل التسليم
            if($order->payment_method == 'card' && !$order->is_paid){
                $payment = $order->payment;
                if ($payment && $payment->status === 'requires_capture') {
                    Stripe::setApiKey(config('services.stripe.secret'));
                    $intent = \Stripe\PaymentIntent::retrieve($payment->payment_intent_id);
                    $amountToCapture = intval(round($payment->amount * 100));
                    $intent->capture(['amount_to_capture' => $amountToCapture]);
                    $payment->update(['status' => 'succeeded']);
                    $order->update(['is_paid' => '1']);
                }
            }
            $order->status = '4'; // تم التوصيل
            $title = 'تم التسليم';
            $body = 'تم تسليم طلبك رقم ' . $order->id;
            $type = 'order_delivered';
            break;

        default:
            return back()->with('warning', '⚠️ لا يمكن تغيير هذه الحالة تلقائيًا.');
    }

    $order->save();

     OrderLog::create([
        'order_id' => $order->id,
        'admin_id' => Auth::user()->id,
        'old_status' => $oldStatus,
        'new_status' => $order->status,
    ]);

    $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
        $order->user,
        $title,
        $body,
        [
            'type' => $type,
            'order_id' => (string) $order->id,
        ]);       

    // إشعار داخل النظام
    Notification::send($order->user, new OrderNotification($order, $type));

    return back()->with('success', '✅ تم تحديث حالة الطلب يدوياً إلى: ' . $order->status);
}


public function reduceDelivery(Request $request, Order $order)
{
    $request->validate([
        'new_delivery_price' => "required|numeric|min:0|max:{$order->delivery_price}",
    ]);

    $newDelivery = $request->new_delivery_price;

    if ($newDelivery >= $order->delivery_price) {
        return back()->with('error', 'يمكنك فقط خفض سعر التوصيل.');
    }

    // الفرق في السعر
    $difference = $order->delivery_price - $newDelivery;

    // تحديث سعر التوصيل
    $order->delivery_price = $newDelivery;

    // تحديث السعر الكلي
    $order->total_price -= $difference;

    // إذا موجود السعر قبل الخصم نحدثه أيضاً
    if ($order->total_before_discount && $order->total_before_discount > $order->total_price) {
        $order->total_before_discount -= $difference;
    }

    // تعديل amount إذا الدفع الكتروني
    if ($order->payment_method == 'card' && $order->payment) {
        $order->payment->amount = $order->total_price;
        $order->payment->save();
    }

    $order->save();

    return back()->with('success', "تم خفض سعر التوصيل بنجاح.");
}


}
