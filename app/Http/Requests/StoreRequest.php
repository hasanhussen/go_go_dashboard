<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
 

 $working_hours = [];

    foreach ($this->all() as $key => $value) {
    if (preg_match('/^working_hours\[(\d+)\]\[(\w+)\]$/', $key, $matches)) {
        $index = $matches[1];
        $field = $matches[2];
        $working_hours[$index][$field] = $value;
    }
  }

    if (!empty($working_hours)) {
        $this->merge(['working_hours' => array_values($working_hours)]);
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
        'name'=> 'required|string|max:255',
        'category_id'=> 'required|exists:categories,id',
        'city_id'=> 'required|integer',
        'delivery'=> 'required|in:0,1', 
        'image'=> 'sometimes|image|mimes:png,jpg,jpeg,gif|max:2048',
        'cover'=> 'sometimes|image|mimes:png,jpg,jpeg,gif|max:4096',
        'special'=> 'sometimes|string',
        'address'=> 'required|string|max:255',
        // 'working_hours' => 'nullable|json',
        'phone'=> 'required|string',
        //'status'=> 'sometimes|in:0,1,2',
        'x'=>'sometimes|string',
        'y'=>'sometimes|string',

        'working_hours' => 'nullable|array',
        'working_hours.*.day' => 'required|string',
        // 'working_hours.*.is_open' => 'boolean',
        // 'working_hours.*.is_24' => 'required|in:0,1,true,false',
        'working_hours.*.open_at' => 'nullable|date_format:H:i',
        'working_hours.*.close_at' => 'nullable|date_format:H:i',

        ];
    }
}
