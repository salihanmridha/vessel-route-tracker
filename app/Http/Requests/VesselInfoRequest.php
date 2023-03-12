<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VesselInfoRequest extends FormRequest
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
            "imoCode" => "required|numeric|digits:7"
        ];
    }

    public function messages()
    {
        return [
            'imoCode.required' => 'The IMO Code that you entered does not exist in our database. Please try again.',
            'imoCode.numeric' => 'The IMO Code that you entered does not exist in our database. Please try again.',
            'imoCode.digits' => 'The IMO Code that you entered does not exist in our database. Please try again.',
        ];
    }
}
