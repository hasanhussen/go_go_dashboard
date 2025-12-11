<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Cart;
use App\Models\Meal;
use App\Models\Additional;
use App\Models\Coupon;
use Stripe\Stripe;
use App\Models\Payment;
use Stripe\StripeClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    public function createOrder(array $validatedData)
    {
        $user_id = Auth::id();
        $validatedData['user_id'] = $user_id;

        return DB::transaction(function () use ($validatedData, $user_id) {

            // جلب عناصر السلة داخل الترانزاكشن لتقليل نافذة السباق
            $cartitems = Cart::where('user_id', $user_id)
                ->whereNull('order_id')
                ->with([
                'meal' => fn($q) => $q->withTrashed()->with(['variants' => fn($v) => $v->withTrashed()]), 
                'additionalItems'])
                ->get();

            // تحقق ونقص الكميات
            foreach ($cartitems as $item) {
                $this->checkAndDecrementItem($item);
            }

            // تحقق الكوبون
            $coupon = null;
            if (isset($validatedData['coupon_id'])) {
                $coupon = $this->applyCoupon($validatedData['coupon_id']);
            }

            // إنشاء الطلب
            $order = Order::create($validatedData);

            // ربط عناصر السلة بالطلب
            Cart::where('user_id', $user_id)
                ->whereNull('order_id')
                ->update(['order_id' => $order->id]);

            if ($coupon) {
                $order->coupon_name = $coupon->name;
                $order->save();
            }

            return $order;
        });
    }

    private function checkAndDecrementItem($item)
    {
        // قفل السطر/المنتج أثناء العمل عليه
        $meal = Meal::withTrashed()
            ->where('id', $item->meal_id)
            ->lockForUpdate()     // 🔥 حماية الكمية
            ->first();

        if (!$meal) {
            throw new \Exception("هناك منتج في السلة غير موجود بعد الآن");
        }
        if ($meal->trashed() || !$meal->is_active) {
            throw new \Exception("المنتج '{$meal->name}' غير متوفر");
        }
        if ($meal->quantity !== null && $meal->quantity < $item->quantity) {
            throw new \Exception("الكمية المطلوبة من المنتج '{$meal->name}' غير متوفرة، المتوفر '{$meal->quantity}'");
        }

        // المقاسات (قفل المقاس عند الحاجة)
        $variant_id = $item->variant_id ?? null;
        if ($variant_id) {
            $variant = $meal->variants()
            ->withTrashed()
                ->where('id', $variant_id)
                ->lockForUpdate()      // 🔒 حماية كمية المقاس
                ->first();

            if (!$variant || $variant->trashed()) {
                throw new \Exception("المقاس المختار للمنتج '{$meal->name}' غير موجود");
            }
            if ($variant->quantity !== null && $variant->quantity < $item->quantity) {
                throw new \Exception("الكمية المطلوبة من المقاس '{$variant->name}' غير متوفرة. المتوفر {$variant->quantity}");
            }
            $variant->decrement('quantity', $item->quantity);
        }

        // نقص كمية الوجبة (الكمية الكلية)
        if ($meal->quantity !== null) {
            $meal->decrement('quantity', $item->quantity);
            $meal->increment('points',   $item->quantity);
        }

        // الإضافات — احصل عليها مع قفل لحماية الكمية
        foreach ($item->additionalItems as $additionalPivot) {
            $additional = Additional::withTrashed()
                ->where('id', $additionalPivot->id)
                ->lockForUpdate()      // 🔒 حماية كمية الإضافة
                ->first();

            $reqQty = $additionalPivot->pivot->quantity ?? 0;

            if (!$additional) {
                throw new \Exception("هناك إضافة في السلة غير موجودة بعد الآن");
            }
            if ($additional->trashed()) {
                throw new \Exception("الإضافة '{$additional->name}' غير متوفرة");
            }
            if ($additional->quantity !== null && $additional->quantity < $reqQty) {
                throw new \Exception("الكمية المطلوبة من الإضافة '{$additional->name}' غير متوفرة، المتوفر '{$additional->quantity}'");
            }
            if ($additional->quantity !== null) {
                $additional->decrement('quantity', $reqQty);
            }
        }
    }

    private function applyCoupon($couponId)
    {
        $coupon = Coupon::where('id', $couponId)
            ->lockForUpdate()      // 🔒 حماية الكوبون والعداد
            ->first();

        if (!$coupon) {
            throw new \Exception("الكوبون غير موجود");
        }
        $status = $coupon->checkStatus();
        if ($status !== 'valid') {
            throw new \Exception("الكوبون غير صالح: {$status}");
        }
        $coupon->decrement('count');
        if ($coupon->count == 0) {
            $coupon->status = 'exhausted';
            $coupon->save();
        }
        return $coupon;
    }

    // ------------------ update order service -----------------------

    public function updateOrder(Order $order, array $validated)
    {
        $user_id = Auth::id();

        return DB::transaction(function () use ($order, $validated, $user_id) {
            $existingCartItems = Cart::where('order_id', $order->id)->get()->keyBy('id');

            // حذف العناصر المحذوفة
            $this->handleDeletedCartItems($existingCartItems, $validated);

            // التعامل مع عناصر الطلب
            foreach ($validated['cart_items'] ?? [] as $itemData) {
                $cartItem = $existingCartItems[$itemData['id']] ?? null;
                $this->handleCartItem($cartItem, $itemData, $order, $user_id);
            }

            // تحديث بيانات الطلب والكوبون
            $this->updateCoupon($order, $validated);

            $order->update([
                'notes' => $validated['notes'] ?? $order->notes,
                'address' => $validated['address'] ?? $order->address,
                'price' => $validated['price'] ?? $order->price,
                'delivery_price' => $validated['delivery_price'] ?? $order->delivery_price,
                'total_price' => $validated['total_price'] ?? $order->total_price,
                'coupon_id' => $validated['coupon_id'] ?? $order->coupon_id,
                'discount' => $validated['discount'] ?? $order->discount,
                'payment_method' => $validated['payment_method'] ?? $order->payment_method,
                'cart_count' => $validated['cart_count'] ?? $order->cart_count,
                'coupon_name' => $order->coupon_name,
                'is_editing' => false,
                'editing_started_at' => null,
                'linked_order_id' => $validated['linked_order_id'] ?? $order->linked_order_id,
                'total_before_discount' => $validated['total_before_discount'] ?? $order->total_before_discount,
            ]);

            return $order;
        });
    }

    protected function handleDeletedCartItems($existingCartItems, $validated)
    {
        $newCartItemIds = collect($validated['cart_items'] ?? [])->pluck('id')->filter();
        $toDelete = $existingCartItems->keys()->diff($newCartItemIds);

        foreach ($toDelete as $cartId) {
            $cartItem = $existingCartItems[$cartId];

            // استرجاع كميات المقاسات
            $variant = $cartItem->meal->variants()->find($cartItem->variant_id ?? null);
            if ($variant && $variant->quantity !== null) {
                $variant->increment('quantity', $cartItem->quantity);
            }

            // استرجاع كمية المنتج الكلية
            if ($cartItem->meal->quantity !== null) {
                $cartItem->meal->increment('quantity', $cartItem->quantity);
                $cartItem->meal->decrement('points',   $cartItem->quantity);
            }

            // استرجاع الإضافات
            foreach ($cartItem->additionalItems as $additional) {
                if ($additional->quantity !== null) {
                    $additional->increment('quantity', $additional->pivot->quantity);
                }
            }

            $cartItem->additionalItems()->detach();
            $cartItem->delete();
        }
    }

    protected function handleCartItem($cartItem, $itemData, $order, $user_id)
    {
        // تجاهل العناصر بدون تعديل
        if ($cartItem && $this->isCartItemUnchanged($cartItem, $itemData)) {
            return;
        }

        // جلب المنتج مع قفل
        $meal = Meal::withTrashed()
            ->where('id', $itemData['meal_id'])
            ->lockForUpdate()     // 🔥 حماية الكمية
            ->first();
        if (!$meal || $meal->trashed() || !$meal->is_active) {
            throw new \Exception("المنتج '{$itemData['meal_id']}' غير متوفر");
        }

        // المقاس
        $variant = null;
        if (!empty($itemData['variant_id'])) {
            $variant = $meal->variants()
            ->withTrashed()
                ->where('id', $itemData['variant_id'])
                ->lockForUpdate()      // 🔒 حماية كمية المقاس
                ->first();

            if (!$variant || $variant->trashed()) {
                throw new \Exception("المقاس المختار للمنتج '{$meal->name}' غير موجود");
            }
        }

        $this->handleVariantQuantity($variant, $cartItem, $itemData, $meal);
        $this->handleMealQuantity($meal, $cartItem, $itemData);
        $this->handleAdditionals($cartItem, $itemData);
        $this->syncCartItem($cartItem, $itemData, $order->id, $user_id);
    }

    protected function isCartItemUnchanged($cartItem, $itemData)
    {
        $reqAdditionals = collect($itemData['additional_items'] ?? [])->map(function ($add) {
            return [
                'id' => $add['id'],
                'pivot' => [
                    'quantity' => $add['pivot']['newquantity'],
                    'old_additional_price' => $add['pivot']['old_additional_price'],
                ],
            ];
        })->toArray();

        $cartAdditionals = $cartItem?->additionalItems->map(function ($add) {
            return [
                'id' => $add->id,
                'pivot' => [
                    'quantity' => $add->pivot->quantity,
                    'old_additional_price' => $add->pivot->old_additional_price,
                ],
            ];
        })->toArray() ?? [];

        return $itemData['newquantity'] === $cartItem->quantity &&
               $itemData['variant_id'] === $cartItem->variant_id &&
               json_encode($reqAdditionals) === json_encode($cartAdditionals);
    }

    protected function handleVariantQuantity($variant, $cartItem, $itemData, $meal)
    {
        if (!$variant) return;

        $oldQty = $cartItem->quantity ?? 0;
        $newQty = $itemData['newquantity'];
        $newTotalQty = $itemData['quantity'];

        // تحقق من وجود الكمية قبل المقارنة
        if ($variant->quantity !== null && $newQty > $variant->quantity) {
            throw new \Exception("الكمية المطلوبة من المقاس '{$variant->name}' للمنتج '{$meal->name}' غير متوفرة. المتوفرة: {$variant->quantity}");
        }

        if ($cartItem) {
            if ($variant->quantity !== null && $variant->quantity < $newQty) {
                throw new \Exception("الكمية المطلوبة من المقاس '{$variant->name}' للمنتج '{$meal->name}' غير متوفرة. المتوفرة: {$variant->quantity}");
            }
            if ($newTotalQty > $oldQty) {
                $variant->decrement('quantity', $newQty);
            } elseif ($newTotalQty < $oldQty) {
                $variant->increment('quantity', $oldQty - $newTotalQty);
            }
        } else {
            // عنصر جديد
            if ($variant->quantity !== null && $variant->quantity < $itemData['quantity']) {
                throw new \Exception("الكمية المطلوبة من المقاس '{$variant->name}' للمنتج '{$meal->name}' غير متوفرة. المتوفرة: {$variant->quantity}");
            }
            if ($variant->quantity !== null) {
                $variant->decrement('quantity', $itemData['quantity']);
            }
        }
    }

    protected function handleMealQuantity($meal, $cartItem, $itemData)
    {
        $oldQty = $cartItem->quantity ?? 0;
        $newQty = $itemData['newquantity'];
        $newTotalQty = $itemData['quantity'];

        if ($cartItem) {
            // تأكد من أن meal->quantity ليس null قبل المقارنة
            if ($meal->quantity !== null && $meal->quantity < $newQty) {
                throw new \Exception("الكمية المطلوبة من المنتج '{$meal->name}' غير متوفرة. المتوفرة: {$meal->quantity}");
            }
            if ($newTotalQty > $oldQty) {
                $meal->decrement('quantity', $newQty);
                $meal->increment('points',   $newQty);
            } elseif ($newTotalQty < $oldQty) {
                $meal->increment('quantity', $oldQty - $newTotalQty);
                $meal->decrement('points',   $oldQty - $newTotalQty);
            }
        } else {
            if ($meal->quantity !== null && $meal->quantity < $itemData['quantity']) {
                throw new \Exception("الكمية المطلوبة من المنتج '{$meal->name}' غير متوفرة. المتوفرة: {$meal->quantity}");
            }
            if ($meal->quantity !== null) {
                $meal->decrement('quantity', $itemData['quantity']);
                $meal->increment('points',   $itemData['quantity']);
            }
        }
    }

    protected function handleAdditionals($cartItem, $itemData)
    {
        $oldAdditionals = $cartItem?->additionalItems->keyBy('id') ?? collect();
        $newAdditionals = collect($itemData['additional_items'] ?? [])->keyBy('id');

        // حذف الإضافات التي اختفت وإرجاع كميتها
        foreach ($oldAdditionals as $oldId => $oldAdd) {
            if (!$newAdditionals->has($oldId)) {
                Additional::withTrashed()->where('id', $oldId)->increment('quantity', $oldAdd->pivot->quantity);
                $cartItem->additionalItems()->detach($oldId);
            }
        }

        // تعامل مع الإضافات الجديدة أو المعدلة مع قفل للـ additional
        foreach ($itemData['additional_items'] ?? [] as $addItem) {
            $additional = Additional::withTrashed()
                ->where('id', $addItem['id'])
                ->lockForUpdate()
                ->first();

            if (!$additional || $additional->trashed()) {
                throw new \Exception("الإضافة '{$addItem['name']}' غير متوفرة");
            }

            $cartAdditionalQty = $cartItem?->additionalItems->find($addItem['id'])->pivot->quantity ?? 0;
            $newTotalQty = $addItem['pivot']['quantity'];
            $newQty = $addItem['pivot']['newquantity'];

            if ($cartItem && $cartItem->additionalItems->contains('id', $addItem['id'])) {
                if ($newTotalQty > $cartAdditionalQty) {
                    if ($additional->quantity !== null && $additional->quantity < $newQty) {
                        throw new \Exception("الكمية المطلوبة من الإضافة '{$additional->name}' غير متوفرة. المتوفرة: {$additional->quantity}");
                    }
                    $additional->decrement('quantity', $newQty);
                } elseif ($newTotalQty < $cartAdditionalQty) {
                    $additional->increment('quantity', $cartAdditionalQty - $newTotalQty);
                }
            } else {
                // إضافة جديدة ضمن الطلب
                if ($additional->quantity !== null && $additional->quantity < $newTotalQty) {
                    throw new \Exception("الكمية المطلوبة من الإضافة '{$additional->name}' غير متوفرة. المتوفرة: {$additional->quantity}");
                }
                $additional->decrement('quantity', $newTotalQty);
            }
        }
    }

    protected function syncCartItem($cartItem, $itemData, $orderId, $user_id)
    {
        if ($cartItem) {
            $cartItem->update([
                'quantity' => $itemData['quantity'],
                'variant_id' => $itemData['variant_id'],
                'old_price' => $itemData['old_price'],
                'old_meal_price' => $itemData['old_meal_price'],
            ]);
        } else {
            $cartItem = Cart::create([
                'user_id' => $user_id,
                'order_id' => $orderId,
                'meal_id' => $itemData['meal_id'],
                'quantity' => $itemData['quantity'],
                'variant_id' => $itemData['variant_id'],
                'old_price' => $itemData['old_price'],
                'old_meal_price' => $itemData['old_meal_price'],
            ]);
        }

        // تحديث الإضافات
        $syncData = [];
        foreach ($itemData['additional_items'] ?? [] as $addItem) {
            $syncData[$addItem['id']] = [
                'quantity' => $addItem['pivot']['newquantity'],
                'old_additional_price' => $addItem['pivot']['old_additional_price'] ?? null,
            ];
        }
        $cartItem->additionalItems()->sync($syncData);
    }

    protected function updateCoupon($order, $validated)
    {
        if (isset($validated['coupon_id']) && $validated['coupon_id'] != $order->coupon_id) {
            $coupon = Coupon::where('id', $validated['coupon_id'])
                ->lockForUpdate()
                ->first();

            if (!$coupon) {
                throw new \Exception("الكوبون غير موجود");
            }

            $status = $coupon->checkStatus();
            if ($status !== 'valid') {
                throw new \Exception("الكوبون غير صالح: {$status}");
            }

            $coupon->decrement('count');
            if ($coupon->count == 0) {
                $coupon->status = 'exhausted';
                $coupon->save();
            }

            $order->coupon_name = $coupon->name;
        }
    }

    // ------------------ delete order service -----------------------

    public function deleteOrder($orderId)
    {
        return DB::transaction(function () use ($orderId) {
            $order = Order::with('carts.meal.variants', 'carts.additionalItems', 'payment')->findOrFail($orderId);

            if ($order->status != '0' && $order->status != '4') {
                throw new \Exception('لا يمكن حذف الطلب الذي تم قبوله أو يتم تحضيره');
            }

            // استرجاع الكوبون إذا موجود
            if ($order->coupon_id) {
                $coupon = Coupon::find($order->coupon_id);
                if ($coupon) {
                    $coupon->increment('count');
                }
            }

            // استرجاع الكميات للوجبات والإضافات
            foreach ($order->carts as $cartItem) {
                $meal = $cartItem->meal;
                if ($meal) {
                    // المقاس المختار
                    if ($cartItem->variant_id) {
                        $variant = $meal->variants()->find($cartItem->variant_id);
                        if ($variant) {
                            $variant->increment('quantity', $cartItem->quantity);
                        }
                    }

                    // كمية المنتج الأساسية
                    if ($meal->quantity !== null) {
                        $meal->increment('quantity', $cartItem->quantity);
                        $meal->decrement('points',   $cartItem->quantity);
                    }

                    // تحديث الكمية الكلية للمنتج = مجموع المقاسات
                    if ($meal->variants()->exists()) {
                        $meal->quantity = $meal->variants()->sum('quantity');
                        $meal->save();
                    }
                }

                // استرجاع الإضافات
                foreach ($cartItem->additionalItems as $additional) {
                    if ($additional && $additional->quantity !== null) {
                        $additional->increment('quantity', $cartItem->pivot->quantity);
                    }
                }
            }

            // إذا الدفع عبر الكارد وما تم الدفع بعد
            if ($order->payment_method == 'card' && !$order->is_paid) {
                $payment = $order->payment;
                if ($payment && $payment->status === 'requires_capture') {
                    Stripe::setApiKey(config('services.stripe.secret'));
                    $intent = \Stripe\PaymentIntent::retrieve($payment->payment_intent_id);
                    $intent->cancel();
                }
            }

            $order->delete();

            return true;
        });
    }

    // ------------------ payment order service -----------------------

    public function validateCartAvailabilityForUser(int $userId)
    {
        $cartItems = Cart::where('user_id', $userId)
            ->whereNull('order_id')
            ->with(['meal' => fn($q) => $q->withTrashed(), 'additionalItems'])
            ->get();

        if ($cartItems->isEmpty()) {
            throw new \Exception("السلة فارغة.");
        }

        foreach ($cartItems as $item) {
            $meal = $item->meal;

            if (!$meal || $meal->trashed() || !$meal->is_active) {
                throw new \Exception("المنتج '{$meal?->name}' غير متوفر.");
            }

            // الكمية الأساسية للوجبة
            if ($meal->quantity !== null && $meal->quantity < $item->quantity) {
                throw new \Exception(
                    "الكمية المطلوبة من المنتج '{$meal->name}' غير متوفرة. المتوفر حالياً: {$meal->quantity}"
                );
            }

            if ($item->variant_id) {
                $variant = $meal->variants()->withTrashed()->find($item->variant_id);

                if (!$variant || $variant->trashed()) {
                    throw new \Exception("المقاس المختار للمنتج '{$meal->name}' غير متوفر.");
                }

                if ($variant->quantity !== null && $variant->quantity < $item->quantity) {
                    throw new \Exception(
                        "الكمية المطلوبة من المقاس '{$variant->name}' غير متوفرة. المتوفر حالياً: {$variant->quantity}"
                    );
                }
            }

            // الإضافات
            foreach ($item->additionalItems as $additional) {
                if ($additional->trashed()) {
                    throw new \Exception(
                        "الإضافة '{$additional->name}' التابعة للمنتج '{$meal->name}' غير متوفرة."
                    );
                }

                $requiredQty = $additional->pivot->quantity;

                if ($additional->quantity !== null && $additional->quantity < $requiredQty) {
                    throw new \Exception(
                        "الكمية المطلوبة من الإضافة '{$additional->name}' غير متوفرة. المتوفر حالياً: {$additional->quantity}"
                    );
                }
            }
        }

        return true; // لو كلشي مرّ بدون مشاكل
    }

    public function validateCartBeforeStripe(Order $order, array $validated)
    {
        foreach ($validated['cart_items'] ?? [] as $itemData) {
            // المنتج
            $meal = Meal::withTrashed()->find($itemData['meal_id']);
            if (!$meal || $meal->trashed() || !$meal->is_active) {
                throw new \Exception("المنتج '{$itemData['meal_id']}' غير متوفر");
            }

            // المقاس
            if (!empty($itemData['variant_id'])) {
                $variant = $meal->variants()->withTrashed()->find($itemData['variant_id']);
                if (!$variant || $variant->trashed()) {
                    throw new \Exception("المقاس غير موجود للمنتج: {$meal->name}");
                }

                if ($variant->quantity !== null && $variant->quantity < $itemData['newquantity']) {
                    throw new \Exception("كمية المقاس '{$variant->name}' غير متوفرة");
                }
            }

            // الكمية الأساسية
            if ($meal->quantity !== null && $meal->quantity < $itemData['newquantity']) {
                throw new \Exception("كمية المنتج '{$meal->name}' غير متوفرة");
            }

            // الإضافات
            foreach ($itemData['additional_items'] ?? [] as $addItem) {
                $additional = Additional::withTrashed()->find($addItem['id']);
                if (!$additional || $additional->trashed()) {
                    throw new \Exception("الإضافة '{$addItem['id']}' غير متوفرة");
                }

                if ($additional->quantity !== null &&
                    $additional->quantity < $addItem['pivot']['newquantity']) {
                    throw new \Exception("كمية الإضافة '{$additional->name}' غير متوفرة");
                }
            }
        }
    }

    public function updatePaymentIntentOnStripe(string $paymentIntentId, $amount)
    {
        $stripe = new StripeClient(config('services.stripe.secret'));
        $stripe->paymentIntents->update($paymentIntentId, [
            'amount' => intval($amount * 100),
        ]);
    }

    public function updateLocalPayment(Order $order, $paymentIntent)
    {
        Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'payment_intent_id' => $paymentIntent->id,
                'amount'            => $paymentIntent->amount,
                'status'            => $paymentIntent->status,
                'currency'          => $paymentIntent->currency,
            ]
        );
    }

}
