<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Allow all for now, or apply auth logic if needed
        return true;
    }

    public function rules()
    {
        return [
            'office_code_id' => 'required|exists:office_codes,id',
            'office_code_budget_id' => 'nullable|exists:office_code_budgets,id', // ✅ Added
            'created_by'     => 'required|exists:users,id',
            'groups'         => 'required|array|min:1',
            'groups.*.group_id' => 'required|exists:group_object_expenditures,id',
            'groups.*.objects'  => 'required|array|min:1',
            'groups.*.objects.*.object_expenditure_id' => 'required|exists:object_expenditures,id',
            'groups.*.objects.*.amount' => 'required|numeric|min:0',
        ];
    }
}