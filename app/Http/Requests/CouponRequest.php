<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        // السماح للجميع (أو خصصها لاحقاً حسب الحاجة)
        return true;
    }

    public function rules(): array
    {
        // نحصل على الكوبون الحالي (في حالة التعديل فقط)
        $couponId = optional($this->route('coupon'))->id;

        return [
            'name' => 'required|string|max:255|unique:coupons,name,' . $couponId,
            'details' => 'required|in:products_price,delivery_price,total_price',
            'discount' => 'required|integer|min:5|max:100',
            'count' => 'required|integer',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after_or_equal:valid_from',
            'notes' => 'nullable|string|max:500', // حقل ملاحظات اختياري
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'يرجى إدخال اسم الكوبون.',
            'name.unique' => 'اسم الكوبون مستخدم مسبقًا.',
            'details.required' => 'يجب تحديد نوع الخصم.',
            'details.in' => 'القيمة المحددة غير صحيحة.',
            'discount.required' => 'يجب إدخال قيمة الخصم.',
            'discount.min' => '%يجب أن تكون قيمة الخصم على الأقل 5.',
            'discount.max' => '%يجب أن تكون قيمة الخصم على الأكثر 100.',
            'count.required' => 'يرجى تحديد عدد مرات استخدام الكوبون.',
            'count.min' => 'عدد الاستخدامات يجب أن يكون على الأقل مرة واحدة.',
            'valid_from.required' => 'يرجى تحديد تاريخ البداية.',
            'valid_to.required' => 'يرجى تحديد تاريخ الانتهاء.',
            'valid_to.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو يساوي تاريخ البداية.',
        ];
    }
}
