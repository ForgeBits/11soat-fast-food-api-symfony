<?php

namespace App\Tests\Feature\Application\Controller\Categories;

use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Infrastructure\Test\Doubles\InMemoryCategoryRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryUpdateTest extends WebTestCase
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

    private function seedCategory(): Category
    {
        $cat = (new Category())
            ->setName('Bebidas')
            ->setDescription('Refrigerantes');
        $this->categoryRepo->seed($cat); // id 1
        return $cat;
    }

    public function test_update_success(): void
    {
        $this->seedCategory();

        $payload = [
            'name' => 'Bebidas 2',
            'description' => 'Atualizado',
        ];

        $this->client->request('PATCH', '/api/categories/1', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('success', $data['status'] ?? null);
        $this->assertSame('Bebidas 2', $data['data']['name'] ?? null);
    }

    public function test_update_validation_error_returns_400(): void
    {
        $this->seedCategory();
        $payload = [
            'name' => '',
        ];

        $this->client->request('PATCH', '/api/categories/1', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
    }

    public function test_update_conflict_when_name_exists_returns_error(): void
    {
        $this->seedCategory(); // id 1, name 'Bebidas'
        $other = (new Category())
            ->setName('Duplicada')
            ->setDescription('Conflicting');
        $this->categoryRepo->seed($other); // id 2

        $payload = [
            'name' => 'Duplicada',
            'description' => 'Try duplicate',
        ];

        $this->client->request('PATCH', '/api/categories/1', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $this->assertSame(500, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('error', $data['status'] ?? null);
        $this->assertStringContainsString('já existe', $data['message'] ?? '');
    }

    public function test_update_not_found_returns_error(): void
    {
        $payload = [
            'name' => 'Nova',
            'description' => 'Desc',
        ];

        $this->client->request('PATCH', '/api/categories/999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $this->assertSame(500, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('error', $data['status'] ?? null);
        $this->assertSame(500, $data['code'] ?? 0);
    }
}
