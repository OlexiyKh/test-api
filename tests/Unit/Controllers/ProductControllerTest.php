<?php

namespace App\Tests\Unit\Controllers;

use App\Application\Controllers\ProductController;
use App\Domain\Entities\Product;
use App\Domain\Enums\CategoryEnum;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Repositories\ProductRepositoryInterface;
use DateTimeImmutable;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ProductControllerTest extends TestCase
{
    private ProductRepositoryInterface $repository;
    private ProductController $controller;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ProductRepositoryInterface::class);
        $this->controller = new ProductController($this->repository);
    }

    public function testGetProduct(): void
    {
        // Create a mock product
        $id = Uuid::uuid4();
        $product = new Product(
            $id,
            'Test Product',
            99.99,
            CategoryEnum::ELECTRONICS,
            ['brand' => 'Test'],
            new DateTimeImmutable()
        );

        // Configure repository mock
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($product);

        // Create request
        $request = new ServerRequest(
            [],
            [],
            '/products/' . $id->toString(),
            'GET'
        );

        // Execute controller
        $response = $this->controller->getProduct($request, ['id' => $id->toString()]);

        // Assert response
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertEquals($id->toString(), $body['id']);
        $this->assertEquals('Test Product', $body['name']);
        $this->assertEquals(99.99, $body['price']);
    }

    public function testGetProductNotFound(): void
    {
        // Create a mock ID
        $id = Uuid::uuid4();

        // Configure repository mock to return null (not found)
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn(null);

        // Create request
        $request = new ServerRequest(
            [],
            [],
            '/products/' . $id->toString(),
            'GET'
        );

        // Expect exception
        $this->expectException(EntityNotFoundException::class);

        // Execute controller
        $this->controller->getProduct($request, ['id' => $id->toString()]);
    }

}
