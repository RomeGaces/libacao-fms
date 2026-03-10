<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupObjectExpenditureRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'group_name' => 'required|string|max:255|unique:group_object_expenditures,group_name',
        ];
    }
}
