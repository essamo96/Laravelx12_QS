<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $decryptedId = null;
        try {
            if ($this->route('id')) {
                $decryptedId = Crypt::decrypt($this->route('id'));
            }
        } catch (DecryptException $e) {
            $decryptedId = null;
        }

            $rules = [
                'name' => 'required|string|max:255',
                'username'=> ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($decryptedId)],
                'email'=> ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($decryptedId)],
                'role_id'=> 'required|numeric|exists:roles,id',
                'status' => 'nullable|in:0,1',
            ];

        // قواعد كلمة المرور
        if ($this->routeIs('users.edit')) {
            $rules['password'] = 'nullable|between:6,16|confirmed';
        } else {
            $rules['password'] = 'required|between:6,16|confirmed';
        }

        return $rules;
    }
}
