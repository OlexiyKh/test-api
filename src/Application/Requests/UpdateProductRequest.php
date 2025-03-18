<?php

namespace App\Application\Requests;

use App\Application\Validators\Types\ArrayType;
use App\Application\Validators\Types\EnumType;
use App\Application\Validators\Types\NumberType;
use App\Application\Validators\Types\StringType;
use App\Domain\Enums\CategoryEnum;

final class UpdateProductRequest
{
    #[StringType(minLength: 3, maxLength: 255)]
    public ?string $name;

    #[NumberType(min: 0.01)]
    public ?float $price;

    #[EnumType(enumClass: CategoryEnum::class)]
    public ?string $category;

    #[ArrayType]
    public ?array $attributes;
}
