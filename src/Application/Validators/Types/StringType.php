<?php

namespace App\Application\Validators\Types;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class StringType
{
    public function __construct(
        public readonly ?int $minLength = null,
        public readonly ?int $maxLength = null
    ) {}

}