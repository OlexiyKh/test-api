<?php

namespace App\Application\Validators\Types;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class NumberType
{
    public function __construct(
        public readonly ?float $min = null,
        public readonly ?float $max = null
    ) {}
}
