<?php

namespace App\Tests\Feature\Application\Controller\Categories;

use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Infrastructure\Test\Doubles\InMemoryCategoryRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryShowTest extends WebTestCase
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

    public function test_get_by_id_success(): void
    {
        $cat = (new Category())
            ->setName('Bebidas')
            ->setDescription('Refrigerantes');
        $this->categoryRepo->seed($cat); // id 1

        $this->client->request('GET', '/api/categories/1');
        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('success', $data['status'] ?? null);
        $this->assertSame('Bebidas', $data['data']['name'] ?? null);
    }

    public function test_get_by_id_not_found_returns_error_with_code_404(): void
    {
        $this->client->request('GET', '/api/categories/999');
        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('error', $data['status'] ?? null);
        $this->assertSame(404, $data['code'] ?? null);
    }
}
