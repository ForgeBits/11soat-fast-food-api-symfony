<?php

namespace App\Application\Domain\Entities\Orders\Entity;

use App\Application\Domain\Entities\Products\Entity\Product;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'order_items')]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Order $order;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Product $product;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'integer')]
    private int $quantity = 1;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $price = '0.00';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $observation = null;

    /**
     * @var Collection<int, OrderItemCustomization>
     */
    #[ORM\OneToMany(mappedBy: 'orderItem', targetEntity: OrderItemCustomization::class, cascade: ['persist', 'remove'])]
    private Collection $customerItems;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
        $this->customerItems = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getOrder(): Order { return $this->order; }
    public function setOrder(Order $order): self { $this->order = $order; return $this; }
    public function getProduct(): Product { return $this->product; }
    public function setProduct(Product $product): self { $this->product = $product; return $this; }
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

    /**
     * @return Collection<int, OrderItemCustomization>
     */
    public function getCustomerItems(): Collection { return $this->customerItems; }
    public function addCustomerItem(OrderItemCustomization $c): self { if (!$this->customerItems->contains($c)) { $this->customerItems->add($c); $c->setOrderItem($this); } return $this; }
    public function removeCustomerItem(OrderItemCustomization $c): self { $this->customerItems->removeElement($c); return $this; }

    public function toArray(): array
    {
        $children = [];
        /** @var OrderItemCustomization $ci */
        foreach ($this->getCustomerItems() as $ci) {
            $children[] = $ci->toArray();
        }
        return [
            'id' => $this->getId(),
            'product_id' => $this->getProduct()->getId(),
            'title' => $this->getTitle(),
            'quantity' => $this->getQuantity(),
            'price' => $this->getPrice(),
            'observation' => $this->getObservation(),
            'customer_items' => $children,
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $this->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
