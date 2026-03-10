<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreObjectExpenditureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    // StoreObjectExpenditureRequest
    public function rules()
    {
        return [
            'group_id'           => 'required|exists:group_object_expenditures,id',
            'object_expenditure' => 'required|string|max:255',
            'account_code'       => 'required|string|max:50',
        ];
    }

}
