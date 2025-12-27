<?php

namespace App\Tests\Feature\Application\Controller\Products;

use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Domain\Entities\Products\Entity\Product;
use App\Infrastructure\Test\Doubles\InMemoryCategoryRepository;
use App\Infrastructure\Test\Doubles\InMemoryProductRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductShowTest extends WebTestCase
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

    public function test_get_by_id_success(): void
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
        $this->productRepo->seed($p);

        $this->client->request('GET', '/api/products/1');
        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('success', $data['status'] ?? null);
        $this->assertSame('X-Burger', $data['data']['name'] ?? null);
        $this->assertArrayHasKey('category', $data['data'] ?? []);
    }

    public function test_get_by_id_not_found_returns_error_with_code_404(): void
    {
        $this->client->request('GET', '/api/products/999');
        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('error', $data['status'] ?? null);
        $this->assertSame(404, $data['code'] ?? null);
    }
}
