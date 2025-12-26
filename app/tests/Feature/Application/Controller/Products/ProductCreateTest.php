<?php

namespace App\Tests\Feature\Application\Controller\Products;

use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Infrastructure\Test\Doubles\InMemoryCategoryRepository;
use App\Infrastructure\Test\Doubles\InMemoryProductRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class ProductCreateTest extends WebTestCase
{
    private KernelBrowser $client;
    private InMemoryProductRepository $productRepo;
    private InMemoryCategoryRepository $categoryRepo;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->productRepo = $container->get(InMemoryProductRepository::class);
        $this->categoryRepo = $container->get(InMemoryCategoryRepository::class);
        $this->productRepo->reset();
        $this->categoryRepo->reset();
    }

    public function test_create_success(): void
    {
        // Seed category id 1
        $category = new Category();
        $category->setName('Lanches');
        $this->categoryRepo->seed($category); // id will be 1

        $payload = [
            'name' => 'X-Burger',
            'description' => 'Delicioso',
            'amount' => 29.9,
            'url_img' => 'https://example.com/x.jpg',
            'customizable' => true,
            'available' => true,
            'category_id' => 1,
        ];

        $this->client->request('POST', '/api/products', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $this->assertResponseIsSuccessful(); // HTTP 200
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('success', $data['status'] ?? null);
        $this->assertEquals('X-Burger', $data['data']['name'] ?? null);
    }

    public function test_create_validation_error_returns_400(): void
    {
        $payload = [
            // 'name' omitted to trigger NotBlank
            'amount' => 10,
            'url_img' => 'invalid-url',
        ];

        $this->client->request('POST', '/api/products', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
    }

    public function test_create_conflict_when_name_exists(): void
    {
        // Seed existing product with same name
        $existingPayload = [
            'name' => 'X-Burger',
            'description' => 'Old',
            'amount' => 25.0,
            'url_img' => 'https://example.com/old.jpg',
            'customizable' => false,
            'available' => true,
            'category_id' => 1,
        ];

        // seed a category so repository can set category reference
        $category = new Category();
        $category->setName('Lanches');
        $this->categoryRepo->seed($category); // id 1

        // Use repository create to add item
        $dto = new \App\Application\Domain\Dtos\Products\CreateProductDto();
        $dto->name = $existingPayload['name'];
        $dto->description = $existingPayload['description'];
        $dto->amount = $existingPayload['amount'];
        $dto->url_img = $existingPayload['url_img'];
        $dto->customizable = $existingPayload['customizable'];
        $dto->available = $existingPayload['available'];
        $dto->category_id = $existingPayload['category_id'];
        $this->productRepo->create($dto);

        $payload = [
            'name' => 'X-Burger',
            'description' => 'Novo',
            'amount' => 29.9,
            'url_img' => 'https://example.com/x.jpg',
            'customizable' => true,
            'available' => true,
            'category_id' => 1,
        ];

        $this->client->request('POST', '/api/products', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $this->assertResponseIsSuccessful(); // current controller returns JsonResponse 200 with error body
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('error', $data['status'] ?? null);
        $this->assertStringContainsString('já existe', $data['message'] ?? '');
    }

    public function test_create_category_not_found_returns_error(): void
    {
        $payload = [
            'name' => 'X-Salada',
            'description' => 'Sem categoria',
            'amount' => 19.9,
            'url_img' => 'https://example.com/xs.jpg',
            'customizable' => false,
            'available' => true,
            'category_id' => 999, // not seeded
        ];

        $this->client->request('POST', '/api/products', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('error', $data['status'] ?? null);
        $this->assertStringContainsString('não existe', $data['message'] ?? '');
    }
}
