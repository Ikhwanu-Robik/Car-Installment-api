<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Validation\Validator;

class InstallmentInvalidFieldException extends Exception
{
    protected $validator;

    protected $code = 401;

    public function __construct(Validator $validator) {
        $this->validator = $validator;
    }

    public function render() {
        return response()->json([
            "message" => "Invalid field",
            "errors" => $this->validator->errors(),
        ], $this->code);
    }
}
