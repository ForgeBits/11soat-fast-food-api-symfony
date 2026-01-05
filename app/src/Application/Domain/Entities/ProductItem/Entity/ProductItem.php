<?php

namespace App\Application\Domain\Entities\ProductItem\Entity;

use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Domain\Entities\Products\Entity\Product;
use App\Infrastructure\Database\Repositories\Products\ProductItemRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductItemRepository::class)]
#[ORM\Table(name: 'product_items')]
class ProductItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'productItems')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Product $product;

    #[ORM\ManyToOne(targetEntity: Item::class)]
    #[ORM\JoinColumn(name: 'item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Item $item;

    #[ORM\Column(type: 'boolean')]
    private bool $essential = false;

    #[ORM\Column(type: 'integer')]
    private int $quantity = 1;

    #[ORM\Column(type: 'boolean')]
    private bool $customizable = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime')]
    private DateTime $updatedAt;

    public function __construct(Product $product, Item $item)
    {
        $this->product = $product;
        $this->item = $item;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getProduct(): Product { return $this->product; }
    public function setProduct(Product $product): self { $this->product = $product; return $this; }
    public function getProductId(): ?int { return $this->product->getId(); }

    public function getItem(): Item { return $this->item; }
    public function setItem(Item $item): self { $this->item = $item; return $this; }
    public function getItemId(): ?int { return $this->item->getId(); }

    public function isEssential(): bool { return $this->essential; }
    public function setEssential(bool $essential): self { $this->essential = $essential; return $this; }

    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $quantity): self { $this->quantity = $quantity; return $this; }

    public function isCustomizable(): bool { return $this->customizable; }
    public function setCustomizable(bool $customizable): self { $this->customizable = $customizable; return $this; }

    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTime { return $this->updatedAt; }
    public function touch(): void { $this->updatedAt = new DateTime(); }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'productId' => $this->getProductId(),
            'itemId' => $this->getItemId(),
            'essential' => $this->isEssential(),
            'quantity' => $this->getQuantity(),
            'customizable' => $this->isCustomizable(),
            'product' => [
                'id' => $this->getProduct()->getId(),
                'name' => $this->getProduct()->getName(),
            ],
            'item' => [
                'id' => $this->getItem()->getId(),
                'name' => $this->getItem()->getName(),
            ],
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $this->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
