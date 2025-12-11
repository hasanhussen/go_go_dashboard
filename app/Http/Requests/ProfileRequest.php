<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
        'name'=> 'required|string|max:255',
        'email'=> 'required|string|max:255',
        'phone'=> 'required|string',
        //'token'=> 'required|string', 
        'gender'=> 'sometimes|integer', 
        //'imgname'=> 'sometimes|string',
        'image'=> 'sometimes|image|mimes:png,jpg,jpeg,gif|max:2048',
        ];
    }
}
