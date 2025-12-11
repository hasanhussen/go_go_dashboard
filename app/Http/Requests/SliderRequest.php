<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SliderRequest extends FormRequest
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
           'slider_title' => 'required|string|max:255',
           'image'=> 'sometimes|image|mimes:png,jpg,jpeg,gif|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'slider_title.required' => ' يجب  ادخال اسم الslider ',
            'image.image' => 'الملف المرفوع يجب أن يكون صورة.',
            'image.mimes' => 'صيغة الصورة يجب أن تكون png, jpg, jpeg, أو gif.',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز 2048 كيلوبايت.',
            'image.required' => 'يجب اختيار صورة.',
        ];
    }
}
