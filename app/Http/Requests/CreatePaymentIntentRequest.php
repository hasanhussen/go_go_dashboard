<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentIntentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // أو حط شرط التحقق من المستخدم إذا لزم
    }

    public function rules(): array
    {
        return [
            'amount'   => 'required|numeric|min:0.5',
            'currency' => 'nullable|in:usd,eur,sar,aed',
            'address'  => 'nullable|string',
            'notes'    => 'nullable|string',
        ];
    }
}
