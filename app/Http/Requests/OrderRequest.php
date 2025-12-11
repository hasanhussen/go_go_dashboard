<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        'notes'=> 'sometimes|string|max:255',
        'address'=>'required|string|max:255',
        'x'=>'sometimes|string',
        'y'=>'sometimes|string',
        'price'=>'required|numeric',
        'delivery_price'=>'required|numeric',
        'delivery_id' => [
        'sometimes',
        Rule::exists('users', 'id')->where(function ($query) {
            $query->where('role', 'delivery');
        }),
    ],
        'coupon_id'=>'sometimes|exists:coupons,id',
        'discount'=>'sometimes|integer',
        'total_price'=>'required|numeric',
        'total_before_discount'=>'sometimes|numeric',
        'payment_method' => 'sometimes|in:cash,card',
        'is_paid' => 'sometimes|in:0, 1',
        'linked_order_id'=>'sometimes|exists:orders,id',
        'cart_count'=>'required|integer',
        'cart_items' => 'sometimes|array',
        'cart_items.*.id' => 'nullable|exists:carts,id',
        'cart_items.*.meal_id' => 'required|exists:meals,id',
        'cart_items.*.quantity' => 'required|integer ',
        'cart_items.*.newquantity' => 'required|integer ',
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
