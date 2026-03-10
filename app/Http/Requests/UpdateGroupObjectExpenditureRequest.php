<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupObjectExpenditureRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'group_name' => 'required|string|max:255|unique:group_object_expenditures,group_name,' . $this->group_object_expenditure->id,
        ];
    }
}