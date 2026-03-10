<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'office_code_id' => 'sometimes|exists:office_codes,id',
            'office_code_budget_id' => 'sometimes|nullable|exists:office_code_budgets,id', // ✅ Added
            'groups' => 'sometimes|array',
            'groups.*.group_id' => 'required_with:groups|exists:group_object_expenditures,id',
            'groups.*.objects' => 'required_with:groups|array',
            'groups.*.objects.*.object_expenditure_id' => 'required|exists:object_expenditures,id',
            'groups.*.objects.*.amount' => 'required|numeric|min:0',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'validation_failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}