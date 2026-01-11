<?php

namespace App\Application\Domain\Entities\Orders\Entity;

use App\Application\Domain\Entities\Orders\Enum\OrderStatus;
use App\Application\Domain\Entities\Products\Entity\Product;
use App\Application\Domain\Entities\Orders\Entity\OrderItem;
use App\Infrastructure\Database\Repositories\Orders\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $clientId = null;

    #[ORM\Column(enumType: OrderStatus::class)]
    private OrderStatus $status = OrderStatus::PENDING;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $amount = '0.00';

    /**
     * @var Collection<int, Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class)]
    #[ORM\JoinTable(name: 'orders_products')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'product_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $products;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['persist', 'remove'])]
    private Collection $items;

    #[ORM\Column(type: 'string', length: 191, unique: true, nullable: true)]
    private ?string $transactionId = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isRandomClient = false;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $codeClientRandom = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $observation = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
        $this->products = new ArrayCollection();
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getClientId(): ?int { return $this->clientId; }
    public function setClientId(?int $clientId): self { $this->clientId = $clientId; return $this; }

    public function getStatus(): OrderStatus { return $this->status; }
    public function setStatus(OrderStatus $status): self { $this->status = $status; return $this; }

    public function getAmount(): float { return (float)$this->amount; }
    public function setAmount(float $amount): self { $this->amount = number_format($amount, 2, '.', ''); return $this; }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection { return $this->products; }
    public function addProduct(Product $product): self { if (!$this->products->contains($product)) { $this->products->add($product); } return $this; }
    public function removeProduct(Product $product): self { $this->products->removeElement($product); return $this; }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getItems(): Collection { return $this->items; }
    public function addItem(OrderItem $item): self { if (!$this->items->contains($item)) { $this->items->add($item); $item->setOrder($this); } return $this; }
    public function removeItem(OrderItem $item): self { $this->items->removeElement($item); return $this; }

    public function getTransactionId(): ?string { return $this->transactionId; }
    public function setTransactionId(?string $transactionId): self { $this->transactionId = $transactionId; return $this; }

    public function isRandomClient(): bool { return $this->isRandomClient; }
    public function setIsRandomClient(bool $isRandomClient): self { $this->isRandomClient = $isRandomClient; return $this; }

    public function getCodeClientRandom(): ?int { return $this->codeClientRandom; }
    public function setCodeClientRandom(?int $codeClientRandom): self { $this->codeClientRandom = $codeClientRandom; return $this; }

    public function getObservation(): ?string { return $this->observation; }
    public function setObservation(?string $observation): self { $this->observation = $observation; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }
    public function touch(): void { $this->updatedAt = new \DateTime(); }

    public function toArray(): array
    {
        $products = [];
        foreach ($this->getProducts() as $p) {
            $products[] = [
                'id' => $p->getId(),
                'name' => $p->getName(),
            ];
        }

        $items = [];
        /** @var OrderItem $it */
        foreach ($this->getItems() as $it) {
            $items[] = $it->toArray();
        }

        return [
            'id' => $this->getId(),
            'client_id' => $this->getClientId(),
            'status' => $this->getStatus()->value,
            'amount' => $this->getAmount(),
            'products' => $products,
            'items' => $items,
            'transaction_id' => $this->getTransactionId(),
            'is_random_client' => $this->isRandomClient(),
            'code_client_random' => $this->getCodeClientRandom(),
            'observation' => $this->getObservation(),
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $this->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
