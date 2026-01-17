<?php

namespace App\Exceptions\Enrollment;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentException extends Exception
{
    /**
     * @param  array<string, array<string>>  $errors
     */
    public function __construct(string $message = '', protected array $errors = [], int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<string, array<string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function render(Request $request): JsonResponse|false
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'errors' => $this->errors,
            ], 422);
        }

        return false;
    }
}
