<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Product;
use App\Domain\Enums\CategoryEnum;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Database\Connection;
use DateTimeImmutable;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Collection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class MongoDBProductRepository implements ProductRepositoryInterface
{
    private Collection $collection;

    public function __construct(Connection $connection)
    {
        $this->collection = $connection->getCollection('products');
    }

    public function find(UuidInterface $id): ?Product
    {
        $document = $this->collection->findOne(['_id' => $id->toString()]);

        if ($document === null) {
            return null;
        }

        return $this->documentToEntity($document);
    }

    public function findAll(array $filters = []): array
    {
        $query = [];

        if (isset($filters['category'])) {
            $query['category'] = $filters['category'];
        }

        if (isset($filters['minPrice'])) {
            $query['price']['$gte'] = (float) $filters['minPrice'];
        }

        if (isset($filters['maxPrice'])) {
            $query['price']['$lte'] = (float) $filters['maxPrice'];
        }

        $cursor = $this->collection->find($query);
        $products = [];

        foreach ($cursor as $document) {
            $products[] = $this->documentToEntity($document);
        }

        return $products;
    }

    public function save(Product $product): void
    {
        $document = [
            '_id' => $product->getId()->toString(),
            'name' => $product->getName(),
            'price' => $product->getPrice(),
            'category' => $product->getCategory()->value,
            'attributes' => $product->getAttributes(),
            'createdAt' => new UTCDateTime($product->getCreatedAt()->getTimestamp() * 1000)
        ];

        $this->collection->updateOne(
            ['_id' => $product->getId()->toString()],
            ['$set' => $document],
            ['upsert' => true]
        );
    }

    public function delete(UuidInterface $id): bool
    {
        $result = $this->collection->deleteOne(['_id' => $id->toString()]);
        return $result->getDeletedCount() > 0;
    }

    private function documentToEntity(array|object $document): Product
    {
        $document = (array) $document;

        return new Product(
            Uuid::fromString($document['_id']),
            $document['name'],
            (float) $document['price'],
            CategoryEnum::fromString($document['category']),
            (array) $document['attributes'],
            new DateTimeImmutable('@' . ($document['createdAt']->toDateTime()->getTimestamp()))
        );
    }
}
