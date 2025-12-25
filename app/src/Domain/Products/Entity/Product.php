<?php
namespace App\Domain\Products\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Domain\Products\Repository\ProductRepository;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:'integer')]
    private ?int $id = null;

    #[ORM\Column(length:150)]
    private string $name;

    #[ORM\Column(type:'text', nullable:true)]
    private ?string $description = null;

    #[ORM\Column(type:'decimal', precision:10, scale:2)]
    private string $amount;

    #[ORM\Column(length:255)]
    private string $urlImg;

    #[ORM\Column(type:'boolean')]
    private bool $customizable = false;

    #[ORM\Column(type:'boolean')]
    private bool $available = true;

    #[ORM\Column(type:'integer', nullable:true)]
    private ?int $categoryId = null;

    #[ORM\Column(type:'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type:'datetime')]
    private \DateTime $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getAmount(): float { return (float)$this->amount; }
    public function setAmount(float $amount): self {
        $this->amount = number_format($amount, 2, '.', '');
        return $this;
    }

    public function getUrlImg(): string { return $this->urlImg; }
    public function setUrlImg(string $urlImg): self { $this->urlImg = $urlImg; return $this; }

    public function isCustomizable(): bool { return $this->customizable; }
    public function setCustomizable(bool $customizable): self { $this->customizable = $customizable; return $this; }

    public function isAvailable(): bool { return $this->available; }
    public function setAvailable(bool $available): self { $this->available = $available; return $this; }

    public function getCategoryId(): ?int { return $this->categoryId; }
    public function setCategoryId(?int $categoryId): self { $this->categoryId = $categoryId; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }

    public function touch(): void { $this->updatedAt = new \DateTime(); }

    public function toArray(): array
    {
        return [
            'id'            => $this->getId(),
            'name'          => $this->getName(),
            'description'   => $this->getDescription(),
            'amount'        => $this->getAmount(),
            'url_img'       => $this->getUrlImg(),
            'customizable'  => $this->isCustomizable(),
            'available'     => $this->isAvailable(),
            'category_id'   => $this->getCategoryId(),
            'created_at'    => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at'    => $this->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
