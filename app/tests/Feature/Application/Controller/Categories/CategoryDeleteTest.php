<?php

namespace App\Tests\Feature\Application\Controller\Categories;

use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Infrastructure\Test\Doubles\InMemoryCategoryRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryDeleteTest extends WebTestCase
{
    private KernelBrowser $client;
    private InMemoryCategoryRepository $categoryRepo;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->categoryRepo = $container->get(InMemoryCategoryRepository::class);
        $this->categoryRepo->reset();
    }

    public function test_delete_success(): void
    {
        $cat = (new Category())
            ->setName('Bebidas');
        $this->categoryRepo->seed($cat); // id 1

        $this->client->request('DELETE', '/api/categories/1');
        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('success', $data['status'] ?? null);
        $this->assertStringContainsString('deleted', $data['message'] ?? '');
    }

    public function test_delete_not_found_returns_404_error(): void
    {
        $this->client->request('DELETE', '/api/categories/999');
        $this->assertSame(404, $this->client->getResponse()->getStatusCode());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('error', $data['status'] ?? null);
        $this->assertSame(404, $data['code'] ?? null);
    }

    public function test_delete_conflict_returns_409_error(): void
    {
        $cat = (new Category())
            ->setName('Bebidas');
        $this->categoryRepo->seed($cat); // id 1
        // Simulate FK violation (category with products)
        $this->categoryRepo->protectWithForeignKey(1);
        $this->client->request('DELETE', '/api/categories/1');

        $this->assertSame(409, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('error', $data['status'] ?? null);
        $this->assertSame(409, $data['code'] ?? null);
    }
}
