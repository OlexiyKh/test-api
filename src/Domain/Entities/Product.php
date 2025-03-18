<?php

namespace App\Domain\Entities;

use App\Domain\Enums\CategoryEnum;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class Product
{
    public function __construct(
        private readonly UuidInterface $id,
        private string $name,
        private float $price,
        private CategoryEnum $category,
        private array $attributes,
        private readonly DateTimeImmutable $createdAt
    ) {}

    public static function create(
        string $name,
        float $price,
        CategoryEnum $category,
        array $attributes
    ): self {
        return new self(
            Uuid::uuid4(),
            $name,
            $price,
            $category,
            $attributes,
            new DateTimeImmutable()
        );
    }

    public function update(string $name = null, float $price = null, CategoryEnum $category = null, array $attributes = null): void
    {
        $this->name = $name ?? $this->name;
        $this->price = $price ?? $this->price;
        $this->category = $category ?? $this->category;

        if ($attributes !== null) {
            $this->attributes = array_merge($this->attributes, $attributes);
        }
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getCategory(): CategoryEnum
    {
        return $this->category;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'name' => $this->name,
            'price' => $this->price,
            'category' => $this->category->value,
            'attributes' => $this->attributes,
            'createdAt' => $this->createdAt->format(DateTimeImmutable::ATOM)
        ];
    }
}
