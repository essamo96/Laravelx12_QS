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
        return [
            'name_ar' => 'required|string|max:191|regex:/^(?!\d)[\p{Arabic}0-9\s]+$/u',
            'name_en' => 'nullable|string|max:191|regex:/^(?!\d)[A-Za-z0-9\s]+$/',
            'description' => 'nullable|string',
            'age' => 'required|integer',
            'big_number' => 'nullable|integer',
            'price' => 'required|numeric',
            'weight' => 'nullable|numeric',
            'status' => 'required|boolean',
            'birth_date' => 'nullable|date',
            'published_at' => 'nullable|date',
            'last_login_at' => 'nullable|date',
            'uuid' => 'nullable|string',
            'options' => 'nullable|string',
            'user_id' => 'nullable|integer|exists:users,id',
            'image' => 'nullable|string|max:191',
            'tags' => 'nullable|string|max:191',
        ];
    }
}

