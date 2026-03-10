<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateObrRequest extends FormRequest
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
        // Get the model instance from the route, e.g., /api/obr-requests/{obrRequest}
        $obrRequest = $this->route('obrRequest');

        return [
            // Fields that might not be sent on update because they are disabled/readonly on the frontend
            'year'       => ['sometimes', 'required', 'integer'],
            'request_id' => ['sometimes', 'required', 'exists:pao_requests,id'],
            'obr_no'     => [
                'sometimes',
                'required',
                'string'
            ],

            // Fields that should always be present on an update
            'office_address' => ['required', 'string', 'max:255'],
            
            // Validation for the array of expenditure items
            'obr_objects'                               => ['required', 'array', 'min:1'],
            
            // If an 'id' is present for a sub-item, it must be a valid integer that exists in the database.
            // 'sometimes' is crucial because new items in an update won't have an 'id'.
            'obr_objects.*.id'                          => ['sometimes', 'integer', 'exists:obr_objects,id'],
            
            'obr_objects.*.object_expenditure_id'       => ['required', 'integer', 'exists:object_expenditures,id'],
            
            // The amount must be a number greater than 0.
            'obr_objects.*.amount'                      => ['required', 'numeric', 'gt:0'],
        ];
    }
}