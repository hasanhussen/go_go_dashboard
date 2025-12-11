<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
        'email'=> 'required|string|max:255|unique:users,email',
        'phone'=> 'required|string|unique:users,phone',
        'gender'=> 'sometimes|integer', 
        'role'=> 'sometimes|string|in:admin,owner,editor,delivery,user',
        'password'=> 'required|string|min:8|confirmed',
        'status'=> 'sometimes|in:0,1,2',
        'fcm_token' => 'nullable|string',
        ];
    }
}
