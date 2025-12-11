<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Http\Requests\CreatePaymentIntentRequest;
use App\Http\Requests\ConfirmOrderRequest;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use App\Models\Additional;
use App\Models\Meal;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Cart;
use App\Services\OrderService;
use App\Models\Coupon;
use Illuminate\Http\Request;

class NewPaymentController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }


    public function getPublishableKey(): JsonResponse
    {
        return response()->json([
            'publishable_key' => config('services.stripe.key'),
        ]);
    }

    // 1) إنشاء PaymentIntent فقط
    public function createPaymentIntent(CreatePaymentIntentRequest $request)
{
    $user_id = Auth::id();

    // فحص سريع للـ cart (check-only)
    try {
        $this->orderService->validateCartAvailabilityForUser($user_id);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 422);
    }

    Stripe::setApiKey(config('services.stripe.secret'));

    $intent = PaymentIntent::create([
        'amount' => intval(round($request->amount * 100)),
        'currency' => $request->currency ?? 'usd',
        'capture_method' => 'manual',
        'metadata' => [
            'user_id' => $user_id,
        ],
    ]);

    return response()->json([
        'client_secret'     => $intent->client_secret,
        'payment_intent_id' => $intent->id,
        'paymentMethodId'=>$intent->payment_method,
        'amount'            => $request->amount,
        'currency'          => $request->currency ?? 'usd',
        'publishable_key'   => config('services.stripe.key'),
    ]);
}


    // 2) تأكيد وإنشاء الطلب بعد نجاح التفويض
    public function confirmOrderAfterPayment(ConfirmOrderRequest $request): JsonResponse
{
    Stripe::setApiKey(config('services.stripe.secret'));
    $intent = PaymentIntent::retrieve($request->payment_intent_id);

    if(!$intent) {
        return response()->json([
            'error' => 'لا يمكن إنشاء الطلب: لم يتم العثور على PaymentIntent.',
        ], 404);
    }

    if ($intent->status !== 'requires_capture') {
            return response()->json([
                'error'  => 'لا يمكن إنشاء الطلب: حالة الدفع ليست محجوزة.',
                'status' => $intent->status,
            ], 400);
        }

    try {
        $data=$request->validated();
        unset($data['payment_intent_id']);
        $order = $this->orderService->createOrder($data);
        
        Payment::create([
            'order_id' => $order->id,
            'payment_intent_id' => $intent->id,
            'stripe_payment_method_id' => $intent->payment_method,
            'status' => $intent->status,
            'amount' => $order->total_price,
        ]);

        return response()->json(['message'=>'تم إنشاء الطلب بعد نجاح التفويض.','order'=>$order]);

    } catch (\Exception $e) {
        try {
            $intent->cancel();
        } catch (\Exception $stripeErr) {
            Log::error("Failed to cancel PaymentIntent {$intent->id}: ".$stripeErr->getMessage());
        }

        return response()->json(['error' => $e->getMessage()], 400);
    }
}


    // 3) القبض عند الجاهزية
  
    public function captureWhenReady($orderId): JsonResponse
{
    $order = Order::findOrFail($orderId);

    if ($order->status !== '0') {
        return response()->json(['error' => 'الطلب ليس في حالة الانتظار.'], 400);
    }

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
    $order->update(['status' => '1', 'is_paid' => '1']);

    return response()->json(['message' => 'تم القبض وتحديث حالة الطلب.']);
}




    public function updateCard($orderId, Request $request): JsonResponse
{
    $order = Order::findOrFail($orderId);
    $payment = $order->payment;

    if (!$payment) {
        return response()->json(['error' => 'لا يوجد عملية دفع مرتبطة بهذا الطلب.'], 400);
    }

    Stripe::setApiKey(config('services.stripe.secret'));

    $intent = \Stripe\PaymentIntent::retrieve($payment->payment_intent_id);

    // الحالة الوحيدة اللي فينا نعدل فيها المبلغ: قبل capture
    if ($intent->status === 'requires_capture') {

        $newTotal = $request->new_total; // المبلغ الجديد بالوحدات العادية (مثلاً بالدولار)
        $oldAmount = $intent->amount;
        $newAmount = intval(round($newTotal * 100));

       
            // Stripe لا يسمح بزيادة المبلغ بعد التفويض
            // فبنلغي القديم وننشئ Intent جديد
            $intent->cancel();

            $newIntent = \Stripe\PaymentIntent::create([
                'amount' => $newAmount,
                'currency' => $intent->currency,
                'capture_method' => 'manual',
                // 'payment_method' => $payment->stripe_payment_method_id, 
                'metadata' => [
                    'order_id' => $order->id,
                    'replacement_for' => $intent->id
                ],
            ]);

            $newPaymentMethodId = $newIntent->payment_method;

            // تحديث البيانات في قاعدة البيانات
            $payment->update([
                'stripe_payment_method_id' => $newPaymentMethodId, 
            ]);

            return response()->json([
                'message' => 'تم إلغاء الدفع القديم وإنشاء تفويض جديد بالمبلغ المحدّث.',
                'new_payment_intent_id' => $newIntent->id,
                'payment_method_id' => $newPaymentMethodId,
                'new_amount' => $newTotal,
                'client_secret' => $newIntent->client_secret,
            ]);
         
    }

    return response()->json([
        'error' => 'لا يمكن تعديل عملية دفع بعد أن تم قبضها أو إلغاؤها.',
        'status' => $intent->status,
    ], 400);
}


public function updateOrderAfterPaymentIntent(
    Order $order,
    ConfirmOrderRequest $request
) {
    $validated = $request->validated();
    $user_id   = Auth::id();

    if (!in_array($validated['payment_method'], ['cash', 'card'])) {
        return response()->json(['error' => 'طريقة الدفع غير صحيحة'], 422);
    }

    try {
       $this->orderService->validateCartBeforeStripe($order, $validated);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 422);
    }

    $paymentIntent = null;
    try {
        $paymentIntent = $this->orderService->updatePaymentIntentOnStripe(
            paymentIntentId: $validated['payment_intent_id'],
            amount: $validated['total_price'] // المتغير الذي اتفقنا عليه
        );
    } catch (\Exception $e) {
        return response()->json(['error' => "Stripe Error: " . $e->getMessage()], 400);
    }

    try {
        $order = $this->orderService->updateOrder($order, $validated);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 422);
    }

    $this->orderService->updateLocalPayment($order, $paymentIntent);

    return response()->json([
        'status'  => 'success',
        'message' => 'Order + PaymentIntent updated successfully',
        'order'   => $order,
    ], 200);
}

}
