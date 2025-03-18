<?php

namespace App\Domain\Exceptions;

use Exception;

final class ValidationException extends Exception
{
    private array $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct('Validation failed: ' . json_encode($errors));
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
