<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfficeCodeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'office_code' => 'required|string|max:255|unique:office_codes,office_code',
            'description' => 'required|string|max:255',
        ];
    }
}
