<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class PermissionRequest extends FormRequest
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
        $id = $this->route('id');

        $decryptedId = null;
        if ($id) {
            try {
                $decryptedId = Crypt::decrypt($id);
            } catch (DecryptException $e) {
                $decryptedId = 0;
            }
        }
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('permissions')->ignore($decryptedId),
            ],
            'guard_name' => 'required|string',
            'group_id' => 'integer',
        ];
    }
}
