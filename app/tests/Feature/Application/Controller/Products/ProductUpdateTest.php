<?php

namespace App\Tests\Feature\Application\Controller\Products;

use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Domain\Entities\Products\Entity\Product;
use App\Infrastructure\Test\Doubles\InMemoryCategoryRepository;
use App\Infrastructure\Test\Doubles\InMemoryProductRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductUpdateTest extends WebTestCase
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

    private function seedProductAndCategory(): array
    {
        $cat = (new Category())->setName('Lanches');
        $this->categoryRepo->seed($cat); // id 1

        $p = (new Product())
            ->setName('X-Burger')
            ->setDescription('Delicioso')
            ->setAmount(29.9)
            ->setUrlImg('https://example.com/x.jpg')
            ->setCustomizable(true)
            ->setAvailable(true)
            ->setCategory($cat);
        $this->productRepo->seed($p); // id 1
        return [$p, $cat];
    }

    public function test_update_success(): void
    {
        [$p, $cat] = $this->seedProductAndCategory();

        $payload = [
            'name' => 'X-Burger 2',
            'description' => 'Melhor ainda',
            'amount' => 31.9,
            'url_img' => 'https://example.com/x2.jpg',
            'customizable' => false,
            'available' => false,
            'category_id' => 1,
        ];

        $this->client->request('PATCH', '/api/products/1', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('success', $data['status'] ?? null);
        $this->assertSame('X-Burger 2', $data['data']['name'] ?? null);
        $this->assertFalse($data['data']['available'] ?? true);
    }

    public function test_update_validation_error_returns_400(): void
    {
        $this->seedProductAndCategory();
        // Missing required fields and invalid url
        $payload = [
            'name' => '',
            'amount' => -1,
            'url_img' => 'not-an-url',
            'customizable' => true,
            'available' => true,
            'category_id' => 1,
        ];

        $this->client->request('PATCH', '/api/products/1', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
    }

    public function test_update_conflict_when_name_exists_returns_error(): void
    {
        [$p, $cat] = $this->seedProductAndCategory();
        // Seed another product with the conflicting name
        $p2 = (new Product())
            ->setName('Duplicado')
            ->setDescription('Old')
            ->setAmount(10)
            ->setUrlImg('https://example.com/old.jpg')
            ->setCustomizable(false)
            ->setAvailable(true)
            ->setCategory($cat);
        $this->productRepo->seed($p2); // id 2

        $payload = [
            'name' => 'Duplicado',
            'description' => 'Try duplicate name',
            'amount' => 31.9,
            'url_img' => 'https://example.com/x2.jpg',
            'customizable' => false,
            'available' => true,
            'category_id' => 1,
        ];

        $this->client->request('PATCH', '/api/products/1', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('error', $data['status'] ?? null);
        $this->assertStringContainsString('já existe', $data['message'] ?? '');
    }

    public function test_update_category_not_found_returns_error(): void
    {
        $this->seedProductAndCategory();
        $payload = [
            'name' => 'X-Burger 2',
            'description' => 'Melhor ainda',
            'amount' => 31.9,
            'url_img' => 'https://example.com/x2.jpg',
            'customizable' => false,
            'available' => true,
            'category_id' => 999,
        ];

        $this->client->request('PATCH', '/api/products/1', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('error', $data['status'] ?? null);
        $this->assertStringContainsString('não existe', $data['message'] ?? '');
    }
}
