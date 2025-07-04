<?php

namespace App\Http\Requests\User\Public\Auth\V1\Auth;

use FalconERP\Skeleton\Models\Erp\People\PeopleDocument;
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'display_name' => 'required|string|max:255',
            'document'     => 'required|string|regex:/^[A-Za-z0-9-]+$/|unique:'.PeopleDocument::class.',value',
            'phone'        => 'required|string|max:255',
            'email'        => 'required|max:255|email:rfc,dns',
        ];
    }
}
