<?php

namespace App\Application\Validators\Types;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class EnumType
{
    public function __construct(
        public readonly string $enumClass
    ) {}
}
