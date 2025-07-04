<?php

namespace App\Http\Requests\Erp\Public\Auth\V1\Auth;

use FalconERP\Skeleton\Models\BackOffice\GiftCode;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'     => 'required|string|max:255',
            'document' => 'required|string|regex:/^[A-Za-z0-9-]+$/',
            'email'    => 'required|max:255|unique:users,email|email:rfc,dns',
            'gift_code' => 'nullable|string|exists:'.GiftCode::class.',code',
        ];
    }
}
