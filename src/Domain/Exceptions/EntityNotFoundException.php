<?php

namespace App\Domain\Exceptions;

use Exception;

final class EntityNotFoundException extends Exception
{
    public function __construct(string $entity, string $id)
    {
        parent::__construct(sprintf('%s with ID %s not found', $entity, $id));
    }
}
