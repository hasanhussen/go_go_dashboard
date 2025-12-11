<?php

namespace App\Services;

use App\Models\Meal;
use App\Http\Requests\OrderRequest;
use Stripe\Stripe;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Additional;
use App\Models\Order;

class OrderProcessingService
{
    /**
     * معالجة جميع بنود السلة (cart_items) لإنشاء أو تعديل الطلب.
     */
    // public function processCartItems(array $cartItems, int $orderId, int $userId)
    // {
    //     foreach ($cartItems as $itemData) {
    //         $this->processSingleItem($itemData, $orderId, $userId);
    //     }
    // }

    /**
     * معالجة عنصر واحد من السلة: جديد أو موجود
     */
    // public function processSingleItem(array $itemData, int $orderId, int $userId)
    // {
    //     $cartItem = Cart::where('id', $itemData['id'] ?? null)
    //         ->where('order_id', $orderId)
    //         ->first();

    //     return $cartItem
    //         ? $this->updateExistingCartItem($cartItem, $itemData)
    //         : $this->createNewCartItem($itemData, $orderId, $userId);
    // }

    public function checkOuantityBeforCreat(array $itemData)
    {
$newmeal = \App\Models\Meal::find($itemData['meal_id']);
        if (!$newmeal) {
            throw new \Exception("هناك منتج في الطلب غير موجود بعد الآن");
       }

        if ($newmeal->trashed() || !$newmeal->is_active) {
            throw new \Exception("المنتج '{$newmeal->name}' غير متوفر");
        }

        if ($newmeal->quantity !== null && $newmeal->quantity < $itemData['quantity']) {
            throw new \Exception("الكمية المطلوبة من المنتج '{$newmeal->name}' غير متوفرة. الكمية المتوفرة حالياً هي {$newmeal->quantity}");
        }

    $variant_id = $item->variant_id ?? null;
    if( $variant_id) {
    $variant = $newmeal->variants()->find($variant_id);
    if (!$variant) {
        throw new \Exception("المقاس المختار للمنتج '{$newmeal->name}' غير موجود");
    }
    if ($variant->quantity !== null && $variant->quantity < $itemData['quantity']) {
        throw new \Exception("الكمية المطلوبة من المقاس '{$variant->name}' للمنتج '{$newmeal->name}' غير متوفرة. الكمية المتوفرة حاليا هي {$variant->quantity}");
     }
    }

        if (!empty($itemData['additional_items'])) {
            foreach ($itemData['additional_items'] as $addItem) {
                $additional = Additional::withTrashed()->find($addItem['id']);
                if(!$additional){
                    throw new \Exception("هناك إضافة في الطلب غير موجودة بعد الآن");
                }
                if ( $additional->trashed()) {
                    throw new \Exception("الإضافة '{$addItem['name']}' غير متوفرة");
                }
                if ($additional->quantity !== null && $additional->quantity < $addItem['pivot']['newquantity']) {
                    throw new \Exception("الكمية المطلوبة من الإضافة '{$additional->name}' التابعة للمنتج '{$newmeal->name}' غير متوفرة. الكمية المتوفرة حالياً هي {$additional->quantity}");
                }
            }
        }
    }

       public function checkOuantityBeforUpdate(array $itemData ,Cart $cartItem)
    {
$meal = $cartItem->meal;
 if ($meal->quantity !== null && $cartItem->quantity < $itemData['quantity'] && $meal->quantity < $itemData['newquantity']) {
    throw new \Exception("الكمية المطلوبة من المنتج '{$meal->name}' غير متوفرة. الكمية المتوفرة حالياً هي {$meal->quantity}");
  }

    $variant_id = $item->variant_id ?? null;
    if( $variant_id) {
   $variant = $meal->variants()->find($variant_id);
    if (!$variant) {
        throw new \Exception("المقاس المختار للمنتج '{$meal->name}' غير موجود");
    }
    if ($variant->quantity !== null && $variant->quantity < $itemData['quantity']) {
        throw new \Exception("الكمية المطلوبة من المقاس '{$variant->name}' للمنتج '{$meal->name}' غير متوفرة. الكمية المتوفرة حاليا هي {$variant->quantity}");
   }

    }

        if (!empty($itemData['additional_items'])) {
        foreach ($itemData['additional_items'] as $addItem) {
            $additional = Additional::withTrashed()->find($addItem['id']);
            $cartItemAdditionals = $cartItem->additionalItems->toArray();

            if(!$additional){
                throw new \Exception("هناك إضافة في الطلب غير موجودة بعد الآن");
           }

            if ($additional->trashed()) {
                throw new \Exception("الإضافة '{$addItem['name']}' غير متوفرة");
            }

            foreach ($cartItemAdditionals as $cartAdditional) {
                if ($cartAdditional['id'] == $addItem['id']) {
                    $currentQuantity = $cartAdditional['pivot']['quantity'];
                    if ($additional->quantity !== null && $currentQuantity< $addItem['pivot']['quantity']&& $additional->quantity < $addItem['pivot']['newquantity']) {
                        throw new \Exception("الكمية المطلوبة من الإضافة '{$additional->name}' التابعة للمنتج '{$meal->name}' غير متوفرة. الكمية المتوفرة حالياً هي {$additional->quantity}");
                   }
                    break;
                    }
                }
            }
        }
    }

    /**
     * إنشاء عنصر جديد داخل الطلب
     */
    public function createNewCartItem(array $itemData, int $orderId, int $userId)
    {
      $this->checkOuantityBeforCreat($itemData);
      $newmeal = \App\Models\Meal::find($itemData['meal_id']);
      $variant_id = $itemData['variant_id'] ?? null;
        // إنشاء CartItem
        $cart = Cart::create([
            'meal_id' => $newmeal->id,
            'quantity' => $itemData['quantity'],
            'variant_id' => $variant_id,
            'old_price' => $itemData['old_price'],
            'old_meal_price' => $itemData['old_meal_price'],
            'user_id' => $userId,
            'order_id' => $orderId,
        ]);

        // مزامنة الإضافات
        if (!empty($itemData['additional_items'])) {
            $sync = [];
            foreach ($itemData['additional_items'] as $add) {
                $sync[$add['id']] = [
                    'quantity' => $add['quantity'],
                    'old_additional_price' => $add['old_additional_price'] ?? null,
                ];
            }
            $cart->additionalItems()->sync($sync);
        }

        // إنقاص الكميات
       // $this->deductQuantities($newmeal, $variant_id, $itemData['quantity'], $itemData['additional_items'] ?? []);

        return $cart;
    }

    /**
     * تحديث عنصر موجود في الطلب
     */
    public function updateExistingCartItem( $cartItems , int $orderId)
    {
        foreach ($cartItems as $itemData) {
    $cartItem = Cart::where('order_id', $orderId)
                ->where('id', $itemData['id'] ?? null)
                ->first();

          
    if (!$cartItem) continue;


        
    $oldAdditionals = $cartItem->additionalItems->keyBy('id');

    $newAdditionals = collect($itemData['additional_items'] ?? [])
        ->keyBy('id');

    foreach ($oldAdditionals as $oldId => $oldAdd) {
        if (!$newAdditionals->has($oldId)) {

            $oldQty = $oldAdd->pivot->quantity;

            Additional::withTrashed()
                ->where('id', $oldId)
                ->increment('quantity', $oldQty);

            $cartItem->additionalItems()->detach($oldId);
        }
    }


        // تحديث الإضافيات إن وُجدت
        if (!empty($itemData['additional_items'])) {
            $syncData = [];
            foreach ($itemData['additional_items'] as $additional) {
                if (isset($additional['pivot'])) {
                    $syncData[$additional['id']] = [
                        'quantity' => $additional['pivot']['quantity'],
                        'old_additional_price' => $additional['pivot']['old_additional_price'] ?? null,
                    ];
                }
            }
            $cartItem->additionalItems()->sync($syncData);
        }

    }
    }

    /**
     * تعديل كمية مقاس أو منتج لعنصر موجود
     */
    // public function updateVariantOrMealQuantity(Cart $cartItem, array $itemData, Meal $meal, ?int $variantId)
    // {
    //     $oldQty = $cartItem->quantity;
    //     $newQty = $itemData['quantity'];

    //     if ($variantId) {
    //         $variant = $meal->variants()->find($variantId);
    //         if (!$variant) throw new \Exception("المقاس غير موجود للمنتج '{$meal->name}'");

    //         if ($newQty > $oldQty) {
    //             $needed = $newQty - $oldQty;
    //             if ($variant->quantity < $needed) throw new \Exception(" الكمية المتوفرة من المقاس '{$variant->name}' غير كافية");
    //             $variant->decrement('quantity', $needed);
    //             $meal->decrement('quantity', $newQty - $oldQty);
    //         } elseif ($newQty < $oldQty) {
    //             $variant->increment('quantity', $oldQty - $newQty);
    //             $meal->increment('quantity', $oldQty - $newQty);
    //         }
    //     } else {
    //         if ($newQty > $oldQty) {
    //             if ($meal->quantity < ($newQty - $oldQty)) throw new \Exception("كمية المنتج '{$meal->name}' غير كافية");
    //             $meal->decrement('quantity', $newQty - $oldQty);
    //         } elseif ($newQty < $oldQty) {
    //             $meal->increment('quantity', $oldQty - $newQty);
    //         }
    //     }
    // }

    /**
     * مزامنة الإضافات
     */
    public function syncAdditionalItems(Cart $cartItem, array $additionals)
    {
        if (empty($additionals)) {
            $cartItem->additionalItems()->detach();
            return;
        }

        $sync = [];
        foreach ($additionals as $add) {
            $sync[$add['id']] = [
                'quantity' => $add['quantity'],
                'old_additional_price' => $add['old_additional_price'] ?? null,
            ];
        }

        $cartItem->additionalItems()->sync($sync);
    }

    /**
     * إنقاص الكميات بعد إنشاء CartItem
     */
    // public function deductQuantities(Meal $meal, ?int $variantId, int $quantity, array $additionals)
    // {
    //     // المقاس أو المنتج
    //     if ($variantId) {
    //         $variant = $meal->variants()->find($variantId);
    //         if ($variant){
    //              $variant->decrement('quantity', $quantity);
    //              $meal->decrement('quantity', $quantity);
    //         }
    //     } else {
    //         if ($meal->quantity !== null) $meal->decrement('quantity', $quantity);
    //     }

    //     // الإضافات
    //     foreach ($additionals as $add) {
    //         $additional = Additional::find($add['id']);
    //         if ($additional && $additional->quantity !== null) {
    //             $additional->decrement('quantity', $add['quantity']);
    //         }
    //     }
    // }


     public function deductQuantities(array $cartItems , int $orderId)
    {
           foreach ($cartItems  as $itemData) {
        $item = Cart::where('order_id', $orderId)
                    ->where('id', $itemData['id'] ?? null)
                    ->first();

          
           if (!$item)
        {
                  
        $newmeal = \App\Models\Meal::find($itemData['meal_id']);
        if ($newmeal->quantity !== null && $newmeal->quantity >= $itemData['quantity']) {
        $newmeal->decrement('quantity', $itemData['quantity']);
    }

    
        $variant_id = $itemData['variant_id'] ?? null;    
        $variant = $newmeal->variants()->find($variant_id);
        if ($variant && $variant->quantity !== null && $variant->quantity >= $itemData['quantity']) {
            $variant->decrement('quantity', $itemData['quantity']);
        }
    

    if (!empty($itemData['additional_items'])) {
        foreach ($itemData['additional_items'] as $addItem) {
            $additional = Additional::find($addItem['id']);
            if ($additional && $additional->quantity !== null && $additional->quantity >= $addItem['pivot']['newquantity']) {
                $additional->decrement('quantity', $addItem['pivot']['newquantity']);
            }
          }
        }
        $newmeal->refreshTotalQuantity();
       }  
        
        $variant_id = $itemData['variant_id'] ?? null; 
        $variant = $item->meal->variants()->find($variant_id);
                if (!$variant) {
                    return response()->json(['error' => "المقاس المختار للمنتج '{$item->meal->name}' غير موجود"], 400);
                }

                // الكمية القديمة للمقاس نفسه (إذا موجود أصلاً بالكارت)
                $old_variant_id = $item->variant_id ?? null;
                $oldVariants = $item->meal->variants()->find($old_variant_id);
                $oldQty = $oldVariants->quantity ?? 0;
                $newQty = $itemData['quantity'];
                
                    if ($itemData['variant_id'] == $old_variant_id) {
                        if ($newQty > $oldQty) {
                    $difference = $newQty - $oldQty;
                    if ($variant->quantity !== null && $variant->quantity < $difference) {
                        return response()->json([
                            'error' => "الكمية المطلوبة من المقاس '{$variant->name}' للمنتج '{$item->meal->name}' غير متوفرة. الكمية المتوفرة حاليًا هي {$variant->quantity}"
                        ], 400);
                    }
                    $variant->decrement('quantity', $difference);
                } elseif ($newQty < $oldQty) {
                    $difference = $oldQty - $newQty;
                    $variant->increment('quantity', $difference);
                }          
            }else {
                $oldVariants->increment('quantity', $oldQty);
                
            }

        if ($item->meal->quantity !== null && $item->meal->quantity >= $item->newquantity && $itemData['quantity'] > $item->quantity) {
            $item->meal->decrement('quantity', $item->newquantity);
        } elseif ($item->meal->quantity !== null && $itemData['quantity'] < $item->quantity) {
            $item->meal->increment('quantity', $item->quantity - $itemData['quantity']);
        }
        if (!empty($itemData['additional_items'])) {
            foreach ($item->additionalItems as $additional) {
                foreach ($itemData['additional_items'] as $addItem) {
                    if ($additional->id == $addItem['id']) {
                        $myadditional = Additional::withTrashed()->find($addItem['id']);
                        if ($myadditional->quantity !== null) {
                            if ($addItem['pivot']['quantity'] > $additional->pivot->quantity) {
                                Additional::withTrashed()->where('id', $addItem['id'])->where('quantity','>=',$addItem['pivot']['newquantity'])->decrement('quantity', $addItem['pivot']['newquantity']);
                            } elseif ($addItem['pivot']['quantity'] < $additional->pivot->quantity) {
                                Additional::withTrashed()->where('id', $addItem['id'])->increment('quantity', $additional->pivot->quantity - $addItem['pivot']['quantity']);
                            }
                        }
                        break;
                    }
                }
            }
        }
        $item->update([
        'quantity' => $itemData['quantity'],
        'old_price' => $itemData['old_price'],
        'old_meal_price' => $itemData['old_meal_price'],
        'variant_id' => $itemData['variant_id']
    ]);
    $item->meal->refreshTotalQuantity();
    }

    }


    public function copounHandling(OrderRequest $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
           if ($request->has('coupon_id') && $request->coupon_id != $order->coupon_id) {
        

        $coupon = Coupon::find($request->coupon_id);


        if (!$coupon) {
            return response()->json(['error' => 'الكوبون غير موجود'], 404);
        }

        $status = $coupon->checkStatus();

          if($status === 'inactive') {
            return response()->json(['error' => 'الكوبون غير مفعل حاليا، يرجى اختيار كوبون آخر'], 400);
        }

        if ($status === 'not_started') {
            return response()->json(['error' => 'الكوبون غير فعال بعد، يرجى المحاولة لاحقًا'], 400);
        }

        if ($status === 'expired') {
            return response()->json(['error' => 'انتهت صلاحية الكوبون'], 400);
        }

        if ($status === 'exhausted') {
            return response()->json(['error' => 'تم استخدام الكوبون لأقصى عدد ممكن'], 400);
        }
    
    }
    }
}
