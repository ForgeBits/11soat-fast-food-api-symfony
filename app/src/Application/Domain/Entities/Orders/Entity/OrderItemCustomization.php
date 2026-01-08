<?php

namespace App\Application\Domain\Entities\Orders\Entity;

use App\Application\Domain\Entities\Items\Entity\Item;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'order_item_customizations')]
class OrderItemCustomization
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: OrderItem::class, inversedBy: 'customerItems')]
    #[ORM\JoinColumn(name: 'order_item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private OrderItem $orderItem;

    #[ORM\ManyToOne(targetEntity: Item::class)]
    #[ORM\JoinColumn(name: 'item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Item $item;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'integer')]
    private int $quantity = 1;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $price = '0.00';

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
    }

    public function getId(): ?int { return $this->id; }
    public function getOrderItem(): OrderItem { return $this->orderItem; }
    public function setOrderItem(OrderItem $orderItem): self { $this->orderItem = $orderItem; return $this; }
    public function getItem(): Item { return $this->item; }
    public function setItem(Item $item): self { $this->item = $item; return $this; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }
    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $quantity): self { $this->quantity = $quantity; return $this; }
    public function getPrice(): float { return (float)$this->price; }
    public function setPrice(float $price): self { $this->price = number_format($price, 2, '.', ''); return $this; }
    public function getObservation(): ?string { return $this->observation; }
    public function setObservation(?string $observation): self { $this->observation = $observation; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }
    public function touch(): void { $this->updatedAt = new \DateTime(); }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'item_id' => $this->getItem()->getId(),
            'title' => $this->getTitle(),
            'quantity' => $this->getQuantity(),
            'price' => $this->getPrice(),
            'observation' => $this->getObservation(),
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $this->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
