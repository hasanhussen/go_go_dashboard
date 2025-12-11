<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Meal;
use App\Models\Additional;

class CartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'meal_id'=> 'required|exists:meals,id',
            'quantity'=>'required|integer',
            'old_price'=>'required|numeric',
            'old_meal_price'=>'required|numeric',
            'additionals' => 'nullable|array',
            'additionals.*.id' => 'required|exists:additionals,id',
            'additionals.*.quantity' => 'required|integer',
            'additionals.*.old_additional_price' => 'required|numeric',
            'variant_id' => 'nullable|exists:meal_variants,id',
            // 'variants.*.id' => 'required|exists:meal_variants,id',
            // 'variants.*.quantity' => 'required|integer ',
        ];
        
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // ✅ التحقق من الكمية المطلوبة مقابل كمية المنتج المتاحة
            $meal = Meal::find($this->meal_id);

            if ($meal && $meal->quantity !=null && $this->quantity > $meal->quantity) {
                $validator->errors()->add('quantity', 'الكمية المطلوبة أكبر من الكمية المتوفرة في المخزون.');
            }

            // ✅ التحقق من الكميات الخاصة بالإضافات
            if ($this->has('additionals')) {
                foreach ($this->additionals as $index => $additional) {
                    $additionalModel = Additional::find($additional['id']);

                    if ($additionalModel && $additionalModel->quantity != null && $additional['quantity'] > $additionalModel->quantity) {
                        $validator->errors()->add(
                            "additionals.$index.quantity",
                            "الكمية المطلوبة من الإضافة رقم #{$additional['id']} أكبر من المتوفرة."
                        );
                    }
                }
            }
        });
    }
}
