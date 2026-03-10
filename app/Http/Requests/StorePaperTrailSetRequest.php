<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaperTrailSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        $setId = $this->route('paperTrailSet') ? $this->route('paperTrailSet')->id : null;

        return [
            'set_no' => ['required', 'integer'],
            'office_code' => ['required', 'string', 'max:255'],
            'id' => ['nullable', 'integer'],

            // Steps validation
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.id' => ['nullable', 'integer'], // ADDED: Allow step IDs
            'steps.*.office_code_id' => [
                'required', 
                'integer',
                'exists:office_codes,id'
            ],
            
            // Internal steps validation
            'steps.*.internal_steps' => ['required', 'array'],
            'steps.*.internal_steps.*.id' => ['nullable', 'integer'], // ADDED: Allow internal step IDs
            'steps.*.internal_steps.*.approval_title' => ['required', 'string', 'max:255'],
        ];
    }
}