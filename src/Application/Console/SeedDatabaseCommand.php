<?php

namespace App\Application\Console;

use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Domain\Entities\ProductItem\Entity\ProductItem;
use App\Application\Domain\Entities\Products\Entity\Product;
use App\Application\Domain\Entities\Orders\Entity\Order;
use App\Application\Domain\Entities\Orders\Entity\OrderItem;
use App\Application\Domain\Entities\Orders\Entity\OrderItemCustomization;
use App\Application\Domain\Entities\Orders\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:seed', description: 'Popula o banco com dados iniciais (categorias, itens, produtos, vínculos e pedidos de exemplo).')]
class SeedDatabaseCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seeding de dados iniciais');

        $this->em->beginTransaction();
        try {
            $io->section('Criando categorias');
            $categoriesByName = [];
            foreach ([
                ['Lanches', 'Itens do cardápio de lanches'],
                ['Bebidas', 'Refrigerantes e sucos'],
                ['Acompanhamentos', 'Porções e acompanhamentos'],
                ['Sobremesas', 'Doces e sorvetes'],
                ['Combos', 'Combinações promocionais'],
            ] as [$name, $desc]) {
                $category = $this->em->getRepository(Category::class)->findOneBy(['name' => $name]);
                if (!$category) {
                    $category = (new Category())
                        ->setName($name)
                        ->setDescription($desc);
                    $this->em->persist($category);
                }
                $categoriesByName[$name] = $category;
            }

            $io->section('Criando itens');
            $itemsByName = [];
            $itemsSeed = [
                ['Carne de Hambúrguer', 'Blend 160g', 10.00, 'https://picsum.photos/seed/carne/400/300'],
                ['Queijo', 'Fatia de queijo prato', 2.50, 'https://picsum.photos/seed/queijo/400/300'],
                ['Bacon', 'Tiras crocantes', 4.00, 'https://picsum.photos/seed/bacon/400/300'],
                ['Alface', 'Folhas verdes', 1.50, 'https://picsum.photos/seed/alface/400/300'],
                ['Tomate', 'Rodelas frescas', 1.50, 'https://picsum.photos/seed/tomate/400/300'],
                ['Cebola', 'Rodelas de cebola', 1.20, 'https://picsum.photos/seed/cebola/400/300'],
                ['Picles', 'Fatias de picles', 1.70, 'https://picsum.photos/seed/picles/400/300'],
                ['Ovo', 'Ovo frito', 2.50, 'https://picsum.photos/seed/ovo/400/300'],
                ['Molho Especial', 'Receita da casa', 1.90, 'https://picsum.photos/seed/molho/400/300'],
                ['Batata Frita Média', 'Porção 300g', 13.00, 'https://picsum.photos/seed/batata/400/300'],
                ['Refrigerante Lata', '350ml', 7.00, 'https://picsum.photos/seed/refri/400/300'],
                ['Nuggets 6un', 'Seis unidades de frango', 12.00, 'https://picsum.photos/seed/nuggets/400/300'],
                ['Sundae Chocolate', 'Sobremesa gelada', 9.50, 'https://picsum.photos/seed/sundae/400/300'],
                ['Milkshake Chocolate', '400ml', 14.90, 'https://picsum.photos/seed/milkshake/400/300'],
                ['Suco de Laranja', '300ml natural', 8.50, 'https://picsum.photos/seed/suco/400/300'],
            ];

            foreach ($itemsSeed as [$name, $desc, $price, $img]) {
                /** @var Item|null $item */
                $item = $this->em->getRepository(Item::class)->findOneBy(['name' => $name]);
                if (!$item) {
                    $item = (new Item())
                        ->setName($name)
                        ->setDescription($desc)
                        ->setPrice($price)
                        ->setUrlImg($img)
                        ->setAvailable(true);
                    $this->em->persist($item);
                }
                $itemsByName[$name] = $item;
            }

            $io->section('Criando produtos');
            $productsByName = [];
            $productsSeed = [
                ['X-Burger', 'Pão, carne e queijo', 29.90, 'https://picsum.photos/seed/xburger/800/600', true, true, 'Lanches'],
                ['X-Bacon', 'Hambúrguer com bacon', 31.90, 'https://picsum.photos/seed/xbacon/800/600', true, true, 'Lanches'],
                ['X-Salada', 'Hambúrguer com salada', 30.90, 'https://picsum.photos/seed/xsalada/800/600', true, true, 'Lanches'],
                ['X-Egg', 'Hambúrguer com ovo', 33.90, 'https://picsum.photos/seed/xegg/800/600', true, true, 'Lanches'],
                ['Batata Frita Média', 'Porção média de batata', 13.00, 'https://picsum.photos/seed/batataprod/800/600', false, true, 'Acompanhamentos'],
                ['Nuggets 6un', 'Porção de nuggets', 12.00, 'https://picsum.photos/seed/nuggetsprod/800/600', false, true, 'Acompanhamentos'],
                ['Refrigerante Lata', '350ml', 7.00, 'https://picsum.photos/seed/refriprod/800/600', false, true, 'Bebidas'],
                ['Suco de Laranja', 'Natural 300ml', 8.50, 'https://picsum.photos/seed/sucoprod/800/600', false, true, 'Bebidas'],
                ['Milkshake Chocolate', '400ml', 14.90, 'https://picsum.photos/seed/milkshakeprod/800/600', false, true, 'Bebidas'],
                ['Sundae Chocolate', 'Sobremesa gelada', 9.50, 'https://picsum.photos/seed/sundaeprod/800/600', false, true, 'Sobremesas'],
                ['Combo X-Bacon + Refri', 'X-Bacon com refrigerante', 36.90, 'https://picsum.photos/seed/combo1/800/600', false, true, 'Combos'],
            ];

            foreach ($productsSeed as [$name, $desc, $amount, $img, $customizable, $available, $categoryName]) {
                /** @var Product|null $product */
                $product = $this->em->getRepository(Product::class)->findOneBy(['name' => $name]);
                if (!$product) {
                    $product = (new Product())
                        ->setName($name)
                        ->setDescription($desc)
                        ->setAmount($amount)
                        ->setUrlImg($img)
                        ->setCustomizable($customizable)
                        ->setAvailable($available)
                        ->setCategory($categoriesByName[$categoryName]);
                    $this->em->persist($product);
                }
                $productsByName[$name] = $product;
            }

            $io->section('Criando vínculos de ProductItem');
            // X-Burger
            $this->ensureProductItem($productsByName['X-Burger'], $itemsByName['Carne de Hambúrguer'], essential: true, quantity: 1, customizable: false);
            $this->ensureProductItem($productsByName['X-Burger'], $itemsByName['Queijo'], essential: true, quantity: 1, customizable: false);
            $this->ensureProductItem($productsByName['X-Burger'], $itemsByName['Alface'], essential: false, quantity: 1, customizable: true);
            $this->ensureProductItem($productsByName['X-Burger'], $itemsByName['Tomate'], essential: false, quantity: 1, customizable: true);
            // X-Bacon
            $this->ensureProductItem($productsByName['X-Bacon'], $itemsByName['Carne de Hambúrguer'], essential: true, quantity: 1, customizable: false);
            $this->ensureProductItem($productsByName['X-Bacon'], $itemsByName['Queijo'], essential: true, quantity: 1, customizable: false);
            $this->ensureProductItem($productsByName['X-Bacon'], $itemsByName['Bacon'], essential: true, quantity: 1, customizable: false);
            $this->ensureProductItem($productsByName['X-Bacon'], $itemsByName['Alface'], essential: false, quantity: 1, customizable: true);
            $this->ensureProductItem($productsByName['X-Bacon'], $itemsByName['Molho Especial'], essential: false, quantity: 1, customizable: true);
            // X-Salada
            $this->ensureProductItem($productsByName['X-Salada'], $itemsByName['Carne de Hambúrguer'], essential: true, quantity: 1, customizable: false);
            $this->ensureProductItem($productsByName['X-Salada'], $itemsByName['Queijo'], essential: true, quantity: 1, customizable: false);
            $this->ensureProductItem($productsByName['X-Salada'], $itemsByName['Alface'], essential: false, quantity: 1, customizable: true);
            $this->ensureProductItem($productsByName['X-Salada'], $itemsByName['Tomate'], essential: false, quantity: 1, customizable: true);
            $this->ensureProductItem($productsByName['X-Salada'], $itemsByName['Cebola'], essential: false, quantity: 1, customizable: true);
            // X-Egg
            $this->ensureProductItem($productsByName['X-Egg'], $itemsByName['Carne de Hambúrguer'], essential: true, quantity: 1, customizable: false);
            $this->ensureProductItem($productsByName['X-Egg'], $itemsByName['Queijo'], essential: true, quantity: 1, customizable: false);
            $this->ensureProductItem($productsByName['X-Egg'], $itemsByName['Ovo'], essential: true, quantity: 1, customizable: false);
            $this->ensureProductItem($productsByName['X-Egg'], $itemsByName['Picles'], essential: false, quantity: 1, customizable: true);

            $io->section('Criando pedidos de exemplo');
            $this->ensureOrders($productsByName, $itemsByName);

            $this->em->flush();
            $this->em->commit();

            $io->success('Seeding concluído com sucesso.');
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->em->rollback();
            $io->error('Falha ao executar seeding: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function ensureProductItem(Product $product, Item $item, bool $essential, int $quantity, bool $customizable): void
    {
        $repo = $this->em->getRepository(ProductItem::class);
        /** @var ProductItem|null $existing */
        $existing = $repo->findOneBy(['product' => $product, 'item' => $item]);
        if ($existing) {
            return;
        }
        $pi = new ProductItem($product, $item);
        $pi->setEssential($essential)
            ->setQuantity($quantity)
            ->setCustomizable($customizable);
        $this->em->persist($pi);
    }

    private function ensureOrders(array $productsByName, array $itemsByName): void
    {
        $this->createOrderIfMissing(
            transactionId: 'seed-tx-0001',
            status: OrderStatus::PENDING,
            amount: 31.90 + 1.50, // produto + alface extra
            items: [
                [
                    'product' => $productsByName['X-Bacon'],
                    'title' => 'X-Bacon',
                    'quantity' => 1,
                    'price' => 31.90,
                    'observation' => 'Ponto bem passado',
                    'customizations' => [
                        ['item' => $itemsByName['Alface'], 'title' => 'Alface extra', 'quantity' => 1, 'price' => 1.50, 'observation' => 'Folhas menores'],
                    ],
                ],
            ],
        );

        $this->createOrderIfMissing(
            transactionId: 'seed-tx-0002',
            status: OrderStatus::DONE,
            amount: 13.00,
            items: [
                [
                    'product' => $productsByName['Batata Frita Média'],
                    'title' => 'Batata Frita Média',
                    'quantity' => 1,
                    'price' => 13.00,
                    'observation' => null,
                    'customizations' => [],
                ],
            ],
        );

        // Pedido pago com dois itens e sem customizações
        $this->createOrderIfMissing(
            transactionId: 'seed-tx-0003',
            status: OrderStatus::PAID,
            amount: 29.90 + 7.00, // X-Burger + Refri
            items: [
                [
                    'product' => $productsByName['X-Burger'],
                    'title' => 'X-Burger',
                    'quantity' => 1,
                    'price' => 29.90,
                    'observation' => null,
                    'customizations' => [],
                ],
                [
                    'product' => $productsByName['Refrigerante Lata'],
                    'title' => 'Refrigerante Lata',
                    'quantity' => 1,
                    'price' => 7.00,
                    'observation' => 'Coca-cola',
                    'customizations' => [],
                ],
            ],
        );

        // Pedido cancelado (exemplo com X-Salada + customização permitida)
        $this->createOrderIfMissing(
            transactionId: 'seed-tx-0004',
            status: OrderStatus::CANCELED,
            amount: 30.90 + 1.20, // X-Salada + cebola extra
            items: [
                [
                    'product' => $productsByName['X-Salada'],
                    'title' => 'X-Salada',
                    'quantity' => 1,
                    'price' => 30.90,
                    'observation' => 'Sem tomate',
                    'customizations' => [
                        ['item' => $itemsByName['Cebola'], 'title' => 'Cebola extra', 'quantity' => 1, 'price' => 1.20, 'observation' => null],
                    ],
                ],
            ],
        );

        // Pedido que falhou (FAILED) com milkshake
        $this->createOrderIfMissing(
            transactionId: 'seed-tx-0005',
            status: OrderStatus::FAILED,
            amount: 14.90,
            items: [
                [
                    'product' => $productsByName['Milkshake Chocolate'],
                    'title' => 'Milkshake Chocolate',
                    'quantity' => 1,
                    'price' => 14.90,
                    'observation' => null,
                    'customizations' => [],
                ],
            ],
        );

        // Em preparação com dois itens e customização válida
        $this->createOrderIfMissing(
            transactionId: 'seed-tx-0006',
            status: OrderStatus::IN_PREPARATION,
            amount: 33.90 + 1.70 + 8.50, // X-Egg + picles extra + suco
            items: [
                [
                    'product' => $productsByName['X-Egg'],
                    'title' => 'X-Egg',
                    'quantity' => 1,
                    'price' => 33.90,
                    'observation' => null,
                    'customizations' => [
                        ['item' => $itemsByName['Picles'], 'title' => 'Picles extra', 'quantity' => 1, 'price' => 1.70, 'observation' => null],
                    ],
                ],
                [
                    'product' => $productsByName['Suco de Laranja'],
                    'title' => 'Suco de Laranja',
                    'quantity' => 1,
                    'price' => 8.50,
                    'observation' => 'Sem açúcar',
                    'customizations' => [],
                ],
            ],
        );

        // Pronto para entregar (READY_TO_DELIVER) com acompanhamento
        $this->createOrderIfMissing(
            transactionId: 'seed-tx-0007',
            status: OrderStatus::READY_TO_DELIVER,
            amount: 29.90 + 13.00, // X-Burger + batata
            items: [
                [
                    'product' => $productsByName['X-Burger'],
                    'title' => 'X-Burger',
                    'quantity' => 1,
                    'price' => 29.90,
                    'observation' => null,
                    'customizations' => [],
                ],
                [
                    'product' => $productsByName['Batata Frita Média'],
                    'title' => 'Batata Frita Média',
                    'quantity' => 1,
                    'price' => 13.00,
                    'observation' => 'Com maionese',
                    'customizations' => [],
                ],
            ],
        );

        // Concluído (DONE) com nuggets e sundae
        $this->createOrderIfMissing(
            transactionId: 'seed-tx-0008',
            status: OrderStatus::DONE,
            amount: 12.00 + 9.50,
            items: [
                [
                    'product' => $productsByName['Nuggets 6un'],
                    'title' => 'Nuggets 6un',
                    'quantity' => 1,
                    'price' => 12.00,
                    'observation' => null,
                    'customizations' => [],
                ],
                [
                    'product' => $productsByName['Sundae Chocolate'],
                    'title' => 'Sundae Chocolate',
                    'quantity' => 1,
                    'price' => 9.50,
                    'observation' => null,
                    'customizations' => [],
                ],
            ],
        );
    }

    /**
     * @param array<int, array{product: Product,title: string,quantity: int,price: float,observation: ?string,customizations: array<int, array{item: Item,title: string,quantity: int,price: float,observation: ?string}>}> $items
     */
    private function createOrderIfMissing(string $transactionId, OrderStatus $status, float $amount, array $items): void
    {
        /** @var Order|null $existing */
        $existing = $this->em->getRepository(Order::class)->findOneBy(['transactionId' => $transactionId]);
        if ($existing) {
            return; // idempotente
        }

        $order = new Order();
        $order->setStatus($status);
        $order->setAmount($amount);
        $order->setTransactionId($transactionId);
        $order->setIsRandomClient(true);
        $order->setCodeClientRandom(1000 + random_int(0, 8999));
        $order->setObservation(null);

        $this->em->persist($order);

        foreach ($items as $it) {
            $oi = new OrderItem();
            $oi->setOrder($order);
            $oi->setProduct($it['product']);
            $oi->setTitle($it['title']);
            $oi->setQuantity($it['quantity']);
            $oi->setPrice($it['price']);
            $oi->setObservation($it['observation']);
            $this->em->persist($oi);

            foreach ($it['customizations'] as $cz) {
                $oic = new OrderItemCustomization();
                $oic->setOrderItem($oi);
                $oic->setItem($cz['item']);
                $oic->setTitle($cz['title']);
                $oic->setQuantity($cz['quantity']);
                $oic->setPrice($cz['price']);
                $oic->setObservation($cz['observation']);
                $this->em->persist($oic);
            }
        }
    }
}
