<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreObrRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow the request; add auth logic here if needed.
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
            // Core OBR fields
            'year'               => 'required|integer|digits:4|min:2000|max:2100',
            'request_id'         => 'required|exists:pao_requests,id',
            'obr_no'             => 'required|string',
            'office_address'     => 'required|string|max:255',

            // Paper trail selection
            'paper_trail_set_id' => 'required|exists:sets,id',

            // OBR items
            'obr_objects'                            => 'required|array|min:1',
            'obr_objects.*.object_expenditure_id'    => 'required|integer|exists:object_expenditures,id|distinct',
            'obr_objects.*.amount'                   => 'required|numeric|min:0',
        ];
    }
}
