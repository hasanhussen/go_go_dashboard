<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_intent_id' => 'required|string',
            'address'           => 'required|string',
            'notes'             => 'nullable|string',
            'x'                 => 'nullable|string',
            'y'                 => 'nullable|string',
            'delivery_price'    => 'required|numeric|min:0',
            'coupon_id'         => 'nullable|exists:coupons,id',
            'discount'          => 'nullable|integer|min:0',
            'price'             => 'required|numeric|min:0',
            'total_price'       => 'required|numeric|min:0',
            'total_before_discount'=>'sometimes|numeric',
            'linked_order_id'=>'sometimes|exists:orders,id',
            // 'currency'          => 'nullable|in:usd,eur,sar,aed',
            'cart_count'=>'required|integer',

            'cart_items' => 'sometimes|array',
            'cart_items.*.id' => 'nullable|exists:carts,id',
            'cart_items.*.meal_id' => 'required|exists:meals,id',
            'cart_items.*.quantity' => 'required|integer ',
            'cart_items.*.newquantity' => 'required|integer',
            'cart_items.*.old_price' => 'required|numeric',
            'cart_items.*.old_meal_price' => 'required|numeric',

            // لو في إضافيات ضمن pivot:
            'cart_items.*.additional_items' => 'nullable|array',
            'cart_items.*.additional_items.*.id' => 'required|exists:additionals,id',
            'cart_items.*.additional_items.*.pivot' => 'sometimes|array',
            'cart_items.*.additional_items.*.pivot.quantity' => 'required|integer',
            'cart_items.*.additional_items.*.pivot.newquantity' => 'required|integer',
            'cart_items.*.additional_items.*.pivot.old_additional_price' => 'required|numeric',
            'cart_items.*.variant_id'=> 'nullable|exists:meal_variants,id',
            // 'cart_items.*.variants.*.id' => 'required|exists:meal_variants,id',
            // 'cart_items.*.variants.*.quantity' => 'required|integer ',
        ];
    }
}
