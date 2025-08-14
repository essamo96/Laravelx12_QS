<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $roleId = $this->route('id');

        if ($this->isMethod('post') && $this->routeIs('roles.add')) {
            return [
                'name' => 'required|unique:roles,name',
                'status' => 'in:0,1',
                'is_user' => 'in:0,1',
            ];
        }

        // إذا كان الطلب للتعديل
        if ($this->isMethod('post') && $this->routeIs('roles.edit')) {
             return [
                'name' => 'required|string|max:255|unique:roles,name,' . $roleId,
                'is_user' => 'nullable|numeric|in:0,1',
                'status' => 'nullable|numeric|in:0,1',
            ];
        }

        return [];
    }
}
