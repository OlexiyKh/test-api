<?php

use App\Domain\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Repositories\MongoDBProductRepository;
use Psr\Container\ContainerInterface;
use MongoDB\Client;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

return [
    Connection::class => function(): Connection {
        $client = new Client($_ENV['MONGODB_URI']);
        $db = $client->selectDatabase($_ENV['MONGODB_DB']);
        return new Connection($db);
    },

    ProductRepositoryInterface::class => function(ContainerInterface $c): ProductRepositoryInterface {
        return new MongoDBProductRepository($c->get(Connection::class));
    },

    Serializer::class => function(): Serializer {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        return new Serializer($normalizers, $encoders);
    }
];
