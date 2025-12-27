<?php

namespace App\Tests\Feature\Application\Controller\Products;

use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Domain\Entities\Products\Entity\Product;
use App\Infrastructure\Test\Doubles\InMemoryCategoryRepository;
use App\Infrastructure\Test\Doubles\InMemoryProductRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductListTest extends WebTestCase
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

    public function test_list_empty_returns_empty_items(): void
    {
        $this->client->request('GET', '/api/products');
        $this->assertResponseIsSuccessful();
        $body = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('success', $body['status'] ?? null);
        $this->assertIsArray($body['data']['items'] ?? null);
        $this->assertSame(0, $body['data']['meta']['total'] ?? -1);
    }

    public function test_list_with_items_returns_paginated_structure(): void
    {
        $cat = (new Category())->setName('Lanches');
        $this->categoryRepo->seed($cat); // id 1

        $p1 = (new Product())
            ->setName('X-Burger')
            ->setDescription('Delicioso')
            ->setAmount(29.9)
            ->setUrlImg('https://example.com/x.jpg')
            ->setCustomizable(true)
            ->setAvailable(true)
            ->setCategory($cat);
        $this->productRepo->seed($p1);

        $p2 = (new Product())
            ->setName('X-Salada')
            ->setDescription('Com salada')
            ->setAmount(27.5)
            ->setUrlImg('https://example.com/xs.jpg')
            ->setCustomizable(false)
            ->setAvailable(true)
            ->setCategory($cat);
        $this->productRepo->seed($p2);

        $this->client->request('GET', '/api/products?page=1&limit=10');
        $this->assertResponseIsSuccessful();
        $body = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('success', $body['status'] ?? null);
        $items = $body['data']['items'] ?? [];
        $this->assertCount(2, $items);
        $this->assertArrayHasKey('name', $items[0]);
        $this->assertArrayHasKey('category', $items[0]);
        $this->assertSame(2, $body['data']['meta']['total'] ?? -1);
    }
}
