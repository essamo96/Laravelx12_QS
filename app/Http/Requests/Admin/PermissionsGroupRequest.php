<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class PermissionsGroupRequest extends FormRequest
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

        return [
            'name' => [
                'required',
                'string',
                Rule::unique('permissions_group', 'name')->ignore($decryptedId),
            ],
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'color' => [
                'required',
                'string',
                Rule::in(['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'])
            ],
            'icon' => 'nullable|string',
            'sort' => 'required|numeric',
            'status' => 'nullable|numeric|in:0,1',
            'parent_id' => 'required|numeric'
        ];
    }
}
