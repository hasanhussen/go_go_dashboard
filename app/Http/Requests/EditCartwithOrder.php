<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditCartwithOrder extends FormRequest
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
        'meal_id' => 'required|exists:meals,id',
        'quantity' => 'required|integer',
        'old_price' => 'required|numeric',
        'old_meal_price' => 'required|numeric',

        'additionals' => 'nullable|array',
        'additionals.*.id' => 'required|exists:additionals,id',
        'additionals.*.pivot' => 'required|array',
        'additionals.*.pivot.quantity' => 'required|integer',
        'additionals.*.pivot.old_additional_price' => 'required|numeric',
    ];
}

}
