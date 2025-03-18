<?php

namespace App\Domain\Enums;

use Attribute;

#[Attribute(\Attribute::TARGET_PROPERTY)]
enum CategoryEnum: string
{
    case ELECTRONICS = 'electronics';
    case CLOTHING = 'clothing';
    case FURNITURE = 'furniture';
    case BOOKS = 'books';
    case FOOD = 'food';
    case OTHER = 'other';

    public static function fromString(string $value): self
    {
        return match (strtolower($value)) {
            'electronics' => self::ELECTRONICS,
            'clothing' => self::CLOTHING,
            'furniture' => self::FURNITURE,
            'books' => self::BOOKS,
            'food' => self::FOOD,
            default => self::OTHER,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
