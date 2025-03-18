<?php

namespace App\Application\Controllers;

use App\Application\Requests\CreateProductRequest;
use App\Application\Requests\UpdateProductRequest;
use App\Application\Validators\RequestValidator;
use App\Domain\Entities\Product;
use App\Domain\Enums\CategoryEnum;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Repositories\ProductRepositoryInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

class ProductController
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function createProduct(ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string) $request->getBody(), true);

        $validatedData = $this->validateCreateRequest($data);

        $product = Product::create(
            $validatedData['name'],
            $validatedData['price'],
            CategoryEnum::fromString($validatedData['category']),
            $validatedData['attributes'] ?? []
        );

        $this->productRepository->save($product);

        return new JsonResponse(
            $product->toArray(),
            201
        );
    }

    public function getProduct(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $id = Uuid::fromString($args['id']);
        $product = $this->productRepository->find($id);

        if ($product === null) {
            throw new EntityNotFoundException('Product', $id->toString());
        }

        return new JsonResponse($product->toArray());
    }

    /**
     * @throws ValidationException
     */
    public function updateProduct(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $id = Uuid::fromString($args['id']);
        $product = $this->productRepository->find($id);

        if ($product === null) {
            throw new EntityNotFoundException('Product', $id->toString());
        }

        $data = json_decode((string) $request->getBody(), true);

        $validatedData = $this->validateUpdateRequest($data);

        $product->update(
            $validatedData['name'] ?? null,
            $validatedData['price'] ?? null,
            isset($validatedData['category']) ? CategoryEnum::fromString($validatedData['category']) : null,
            $validatedData['attributes'] ?? null
        );

        $this->productRepository->save($product);

        return new JsonResponse($product->toArray());
    }

    public function deleteProduct(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $id = Uuid::fromString($args['id']);
        $deleted = $this->productRepository->delete($id);

        if (!$deleted) {
            throw new EntityNotFoundException('Product', $id->toString());
        }

        return new JsonResponse(null, 204);
    }

    public function listProducts(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $filters = [];

        if (isset($queryParams['category'])) {
            $filters['category'] = $queryParams['category'];
        }

        if (isset($queryParams['minPrice'])) {
            $filters['minPrice'] = (float) $queryParams['minPrice'];
        }

        if (isset($queryParams['maxPrice'])) {
            $filters['maxPrice'] = (float) $queryParams['maxPrice'];
        }

        $products = $this->productRepository->findAll($filters);
        $result = array_map(fn(Product $product) => $product->toArray(), $products);

        return new JsonResponse($result);
    }

    /**
     * @throws ValidationException
     */
    private function validateCreateRequest(array $data): array
    {
        return RequestValidator::validate($data, CreateProductRequest::class);
    }

    /**
     * @throws ValidationException
     */
    private function validateUpdateRequest(array $data): array
    {
        return RequestValidator::validate($data, UpdateProductRequest::class);
    }
}
