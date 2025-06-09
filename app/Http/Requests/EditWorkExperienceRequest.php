<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditWorkExperienceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company' => 'bail|required',
            'position' => 'bail|required',
            'year_started' => 'bail|required',
            'year_ended' => 'bail|required'
        ];
    }

    public function messages() {
        return
        [
            'required' => 'Field is required.'
        ];
    }
}
