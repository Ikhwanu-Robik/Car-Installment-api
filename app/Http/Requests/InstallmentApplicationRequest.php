<?php

namespace App\Http\Requests;

use App\Exceptions\InstallmentInvalidFieldException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class InstallmentApplicationRequest extends FormRequest
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
        return [
            "instalment_id" => "required|exists:installment,id",
            "available_month_id" => "required|exists:available_month,id",
            "notes" => "required|string"
        ];
    }

    protected function failedValidation(Validator $validator) {
        throw new InstallmentInvalidFieldException($validator);
    }
}
