<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MealRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        // تحويل JSON string الموجود في الحقل "data" إلى array
        if ($this->has('data')) {
            $data = json_decode($this->input('data'), true);
            if (is_array($data)) {
                $this->merge($data); // يدمج البيانات مع الـ request الرئيسي
            }
        }

            $additionals = [];
    foreach ($this->all() as $key => $value) {
        if (preg_match('/^additionals\[(\d+)\]$/', $key)) {
            $additionals[] = $value;
        }
    }

    if (!empty($additionals)) {
        $this->merge(['additionals' => $additionals]);
    }


    $variants = [];

    foreach ($this->all() as $key => $value) {
    if (preg_match('/^variants\[(\d+)\]\[(\w+)\]$/', $key, $matches)) {
        $index = $matches[1];
        $field = $matches[2];
        $variants[$index][$field] = $value;
    }
  }

    if (!empty($variants)) {
        $this->merge(['variants' => array_values($variants)]);
    }

  }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        'store_id'=> 'required|exists:stores,id',
        'name'=> 'required|string|max:255',
        'description'=> 'required|string|max:255',
        // 'note'=> 'nullable|string|max:255', 
        'quantity'=>'sometimes|integer',
        'image'=> 'sometimes|image|mimes:png,jpg,jpeg,gif|max:4096',
        'price'=>'nullable|numeric',
        'additionals' => 'nullable|array',
        'additionals.*' => 'exists:additionals,id',
        'variants' => 'nullable|array',
        'variants.*.name' => 'required_with:variants|string|max:255',
        'variants.*.price' => 'required_with:variants|numeric',
        'variants.*.quantity' => 'required_with:variants|integer|min:0',
        ];
    }
}
