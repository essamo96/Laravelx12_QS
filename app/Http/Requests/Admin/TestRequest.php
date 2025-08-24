<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('id') !== null;
        if ($isUpdate) {
            return [
                'age' => 'nullable|string|min:3',
				'big_number' => 'nullable|string|min:3',
				'birth_date' => 'nullable|string|min:3',
				'description' => 'nullable|string|min:3',
				'last_login_at' => 'nullable|string|min:3',
				'name_ar' => 'regex:/^(?!\d)[\p{Arabic}0-9 ]+$/u|min:3|nullable',
				'name_en' => 'regex:/^(?!\d)[A-Za-z0-9 ]+$/|min:3|nullable',
				'options' => 'nullable|string|min:3',
				'price' => 'nullable|string|min:3',
				'published_at' => 'nullable|string|min:3',
				'status' => 'required|string|min:3',
				'user_id' => 'nullable|string|min:3',
				'uuid' => 'nullable|string|min:3',
				'weight' => 'nullable|string|min:3'
            ];
        }

        return [
            'age' => 'required|string|min:3',
				'big_number' => 'nullable|string|min:3',
				'birth_date' => 'nullable|string|min:3',
				'description' => 'nullable|string|min:3',
				'last_login_at' => 'nullable|string|min:3',
				'name_ar' => 'regex:/^(?!\d)[\p{Arabic}0-9 ]+$/u|min:3|required',
				'name_en' => 'regex:/^(?!\d)[A-Za-z0-9 ]+$/|min:3|nullable',
				'options' => 'nullable|string|min:3',
				'price' => 'required|string|min:3',
				'published_at' => 'nullable|string|min:3',
				'status' => 'required|string|min:3',
				'user_id' => 'nullable|string|min:3',
				'uuid' => 'nullable|string|min:3',
				'weight' => 'nullable|string|min:3'
        ];
    }
}