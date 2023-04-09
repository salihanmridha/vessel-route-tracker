<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VesselMmsiPositionRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            "mmsiCode" => "required|numeric|digits:9"
        ];
    }

    public function messages()
    {
        return [
            'mmsiCode.required' => 'The MMSI Code that you entered does not exist in our database. Please try again.',
            'mmsiCode.numeric' => 'The MMSI Code that you entered does not exist in our database. Please try again.',
            'mmsiCode.digits' => 'The MMSI Code that you entered does not exist in our database. Please try again.',
        ];
    }
}
