<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Http\Requests\OrderRequest;
use Stripe\Stripe;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Additional;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CartRequest;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;

use App\Notifications\OrderNotification;
use App\Notifications\AdminNotification;
use Illuminate\Support\Facades\Notification;
use Kreait\Firebase\Factory;
use App\Events\AccpetOrder;

class NewOrderController extends Controller
{

    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function add(OrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder($request->validated());
            return response()->json([
                'success' => 'تم إرسال الطلب بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }


    public function update($orderId, OrderRequest $request)
    {
        $order = Order::findOrFail($orderId);
        try {
            $updatedOrder = $this->orderService->updateOrder($order, $request->validated());
            return response()->json([
                'success' => 'تم تعديل الطلب بنجاح',
                'order' => $updatedOrder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }


public function delete($orderId)
{
    try {
        
        $this->orderService->deleteOrder($orderId);

        return response()->json(['success' => 'تم حذف الطلب بنجاح'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 400);
    }
}


// public function getOrders(){
//     $user_id = Auth::id();

//         $processingOrders = Order::where('user_id', $user_id)
//                     ->whereIn('status', ['1','2','3'])
//                     ->get();
//         $rejectedOrders = Order::withTrashed()->where('user_id', $user_id)
//                     ->where('deleted_at', '!=', null)
//                     ->get();   
//         $completedOrders = Order::where('user_id', $user_id)
//                     ->where('status', '4')
//                     ->get();
//         $waitingOrders = Order::where('user_id', $user_id)
//                     ->where('status', '0')
//                     ->get();    
                    
//         return response()->json([
//         'processingOrders' => $processingOrders,
//         'rejectedOrders' => $rejectedOrders,
//         'completedOrders' => $completedOrders,
//         'waitingOrders' => $waitingOrders
//     ]);
// }


    public function getProcessing()
    {
        $user_id = Auth::id();

        $orders = Order::where('user_id', $user_id)
                    ->whereIn('status', ['1','2','3'])
                    ->get();

        return response()->json($orders);
    }


        public function getwaiting()
    {
        $user_id = Auth::id();

        $orders = Order::where('user_id', $user_id)
                    ->where('status', '0')
                    ->get();

        return response()->json($orders);
    }


    public function getRejected()
    {
        $user_id = Auth::id();

        $orders = Order::withTrashed()->where('user_id', $user_id)
                    ->where('deleted_at', '!=', null)
                    ->get();

        return response()->json($orders);
    }


    public function getCompleted()
    {
        $user_id = Auth::id();

        $orders = Order::where('user_id', $user_id)
                    ->where('status', '4')
                    ->get();

        return response()->json($orders);
    }



    public function getDetails($orderId)
    {
        $order = Order::with([
            'carts.meal' => function ($query) {
    $query->withTrashed(); 
},
'carts.meal.store' => function ($query) {
    $query->withTrashed(); 
},
            'carts.additionalItems' => function ($query) {
                $query->withTrashed(); // إذا الإضافات فيها softDeletes
            },
            'carts.variant',
            'coupon',
            
        ])->findOrFail($orderId);

        if($order->status=='0' && $order->is_editing == false){
            $order->is_editing = true; // Active
            $order->editing_started_at = now();
            $order->save(); 
    }
    return response()->json($order);
    }


    //delivery

    public function deliveryAccept($id)
    {
    $order = Order::findOrFail($id);

    $user = Auth::user()->load([
    'activeOrders' => function ($query) {
        $query->whereIn('status', ['1', '2', '3']);
    }
]);

     if( $user->activeOrders->count() > 0) {
        return response()->json([
        'error' =>'لا يمكنك الموافقة على أكثر من طلب بنفس الوقت'
    ], 400);
     }


    if( $order->status == '5'&& $order->delivery_id == null){
        $user_id = Auth::id();
        $order->delivery_id = $user_id;
    $order->status = '1'; // Active
        $order->save();

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
     broadcast(new AccpetOrder($order))->toOthers();
    $admin= User::role('admin')->orderBy('created_at', 'asc')->first();
       Notification::send($admin, new AdminNotification($order->user,type: 'order_accept',order:$order)); 

    return response()->json([
            'success' =>'تم قبول الطلب'
        ]); 
    }else{
    return response()->json([
            'error' =>'لقد تمت الموافقة هلى الطلب من عامل آخر'
        ], 400); 
    }

    }

    public function deliveryOnTheWay($id)
    {
        $order = Order::findOrFail($id);
        if(!$order){
        return response()->json([
            'error' =>'الطلب محـذوف'
        ], 404);
    }
        if( $order->status = '1'){
            $order->status = '2'; // Active
        $order->save();

    $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
        $order->user,
        'طلبك قيد التوصيل 🛵',
        ' طلبك رقم ' . $order->id . 'قيد التوصيل',
        [
            'type' => 'order_on_the_way',
            'order_id' => (string) $order->id, // لازم قيم الـ data تكون نصوص
        ]);

        Notification::send($order->user, new OrderNotification($order, 'order_on_the_way'));
        return response()->json([
            'success' =>'طلبك قيدالتوصيل'
        ]);
        }
    }

    public function deliveryOnSite($id)
    {
        $order = Order::findOrFail($id);
        if(!$order){
        return response()->json([
            'error' =>'الطلب محـذوف'
        ], 404);
    }
        if( $order->status = '2'){
            $order->status = '3'; // Active

    $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
        $order->user,
        'طلبك قيد التوصيل 🛵',
        ' طلبك رقم ' . $order->id . 'قيد التوصيل',
        [
            'type' => 'order_on_site',
            'order_id' => (string) $order->id, // لازم قيم الـ data تكون نصوص
        ]);


        Notification::send($order->user, new OrderNotification($order, 'order_on_site'));
 
            $order->save();
        return response()->json([
            'success' =>'عامل التوصيل في الموقع'
        ]);
        }
    }

    public function delivered($id)
    {
        $order = Order::findOrFail($id);
        if( $order->status = '3'){
            if($order->payment_method == 'card' && !$order->is_paid){
        $payment = $order->payment;
        if (!$payment || $payment->status !== 'requires_capture') {
            return response()->json(['error' => 'لا يوجد دفع محجوز للسحب.'], 400);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $intent = \Stripe\PaymentIntent::retrieve($payment->payment_intent_id);

        // لو المبلغ انخفض أثناء التعديل
        $amountToCapture = intval(round($payment->amount * 100));

        $intent->capture(['amount_to_capture' => $amountToCapture]);

        $payment->update(['status' => 'succeeded']);
        $order->update(['is_paid' => '1']);
        }
        $order->status = '4'; // Active
        $order->save();
        $notificationService = new \App\Services\NotificationService();

        $notificationService->sendToUser(
        $order->user,
        'تم التسليم 📦',
        ' تم تسليم طلبك رقم ' . $order->id,
        [
            'type' => 'order_delivered',
            'order_id' => (string) $order->id, // لازم قيم الـ data تكون نصوص
        ]);
        
        Notification::send($order->user, new OrderNotification($order, 'order_delivered'));
    
        return response()->json([
            'success' =>'تم تسليم الطلب بنجاح'
        ]);
        }
    
    }


    public function getDeliveryOrders()
    {
        $user_id = Auth::id();

        $processingOrders = Order::withoutTrashed()->where('delivery_id', $user_id)
                    ->whereIn('status', ['1','2','3'])
                    ->get();
        $completedOrders = Order::withoutTrashed()->where('delivery_id', $user_id)
                    ->where('status', '4')
                    ->get();
        $waitingOrders = Order::withoutTrashed()
            ->where(function ($q) use ($user_id) {
                $q->where('delivery_id', $user_id)
                ->where('status', '0');
            })
            ->orWhere('status', '5') // ترجع كل طلبات الحالة 5 مهما كان الـ delivery
            ->get();    
            
        return response()->json([
        'processingOrders' => $processingOrders,
        'completedOrders' => $completedOrders,
        'waitingOrders' => $waitingOrders
    ]);   
 
        
    }


    // public function getDeliveryProcessing()
    // {
    //     $user_id = Auth::id();

    //     $orders = Order::withoutTrashed()->where('delivery_id', $user_id)
    //                 ->whereIn('status', ['1','2','3'])
    //                 ->get();

    //     return response()->json($orders);
    // }


    // public function getDeliveryCompleted()
    // {
    //     $user_id = Auth::id();

    //     $orders = Order::withoutTrashed()->where('delivery_id', $user_id)
    //                 ->where('status', '4')
    //                 ->get();

    //     return response()->json($orders);
    // }

    // public function getDeliverywaiting()
    // {
    //     $user_id = Auth::id();

    //     $orders = Order::withoutTrashed()
    //         ->where(function ($q) use ($user_id) {
    //             $q->where('delivery_id', $user_id)
    //             ->where('status', '0');
    //         })
    //         ->orWhere('status', '5') // ترجع كل طلبات الحالة 5 مهما كان الـ delivery
    //         ->get();

    //     return response()->json($orders);
    // }


    }
