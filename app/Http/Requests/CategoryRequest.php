<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
        $categoryId = optional($this->route('category'))->id;
        return [
            'type' => 'required|string|max:255|unique:categories,type,' . $categoryId,
           'image'=> 'sometimes|image|mimes:png,jpg,jpeg,gif|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'type.unique' => 'اسم الصنف مستخدم من قبل. يرجى اختيار اسم مختلف.',
            'type.required' => ' يجب  ادخال اسم الصنف ',
            'type.string' => 'اسم الصنف يجب أن يكون نصًا.',
            'type.max' => 'اسم الصنف يجب ألا يتجاوز 255 حرفًا.',
            'image.image' => 'الملف المرفوع يجب أن يكون صورة.',
            'image.mimes' => 'صيغة الصورة يجب أن تكون png, jpg, jpeg, أو gif.',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز 2048 كيلوبايت.',
            'image.required' => 'يجب اختيار صورة.',
        ];
    }
}
