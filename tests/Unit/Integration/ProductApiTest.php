<?php

namespace App\Tests\Unit\Integration;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client as HttpClient;

class ProductApiTest extends TestCase
{
    private HttpClient $client;
    private ?string $testProductId = null;

    protected function setUp(): void
    {
        $this->client = new HttpClient([
            'base_uri' => 'http://localhost:8080/',
            'http_errors' => false
        ]);

        // Clean up test data if any
        if ($this->testProductId) {
            try {
                $this->client->delete('/products/' . $this->testProductId);
            } catch (\Exception $e) {
                // Ignore any cleanup errors
            }
            $this->testProductId = null;
        }
    }

    public function testFullProductLifecycle(): void
    {
        // 1. Create a product
        $createData = [
            'name' => 'Integration Test Product',
            'price' => 129.99,
            'category' => 'electronics',
            'attributes' => [
                'brand' => 'TestBrand',
                'color' => 'blue'
            ]
        ];

        $response = $this->client->post('/products', [
            'json' => $createData
        ]);

        $this->assertEquals(201, $response->getStatusCode());

        $data = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertEquals($createData['name'], $data['name']);

        // Save ID for later tests
        $this->testProductId = $data['id'];

        // 2. Get the product
        $response = $this->client->get('/products/' . $this->testProductId);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals($createData['name'], $data['name']);
        $this->assertEquals($createData['price'], $data['price']);
        $this->assertEquals($createData['category'], $data['category']);
        $this->assertEquals($createData['attributes']['brand'], $data['attributes']['brand']);

        // 3. Update the product
        $updateData = [
            'price' => 149.99,
            'attributes' => [
                'discount' => '10%'
            ]
        ];

        $response = $this->client->patch('/products/' . $this->testProductId, [
            'json' => $updateData
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals($updateData['price'], $data['price']);
        $this->assertEquals($updateData['attributes']['discount'], $data['attributes']['discount']);
        $this->assertEquals($createData['attributes']['brand'], $data['attributes']['brand']); // Original attributes preserved

        // 4. List products with filter
        $response = $this->client->get('/products?category=electronics&minPrice=140');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($data);

        $found = false;
        foreach ($data as $product) {
            if ($product['id'] === $this->testProductId) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Product should be found in filtered list');

        // 5. Delete the product
        $response = $this->client->delete('/products/' . $this->testProductId);

        $this->assertEquals(204, $response->getStatusCode());

        // 6. Confirm product is deleted
        $response = $this->client->get('/products/' . $this->testProductId);
        $this->assertEquals(404, $response->getStatusCode());

        // Clear test product ID since we've deleted it
        $this->testProductId = null;
    }
}
