<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Product;
use Ramsey\Uuid\UuidInterface;

interface ProductRepositoryInterface
{
    public function find(UuidInterface $id): ?Product;

    public function findAll(array $filters = []): array;

    public function save(Product $product): void;

    public function delete(UuidInterface $id): bool;
}
