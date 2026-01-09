<?php

namespace App\Application\Domain\Entities\Items\Entity;

use App\Infrastructure\Database\Repositories\Products\ItemsRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemsRepository::class)]
#[ORM\Table(name: 'items')]
class Item
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
    private string $price;

    #[ORM\Column(type:'boolean')]
    private bool $available = true;

    #[ORM\Column(length:255)]
    private string $urlImg;

    #[ORM\Column(type:'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type:'datetime')]
    private DateTime $updatedAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getPrice(): float { return (float)$this->price; }
    public function setPrice(float $price): self {
        $this->price = number_format($price, 2, '.', '');
        return $this;
    }

    public function getUrlImg(): string { return $this->urlImg; }
    public function setUrlImg(string $urlImg): self { $this->urlImg = $urlImg; return $this; }

    public function isAvailable(): bool { return $this->available; }
    public function setAvailable(bool $available): self { $this->available = $available; return $this; }

    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTime { return $this->updatedAt; }

    public function touch(): void { $this->updatedAt = new DateTime(); }

    public function toArray(): array
    {
        return [
            'id'            => $this->getId(),
            'name'          => $this->getName(),
            'description'   => $this->getDescription(),
            'amount'        => $this->getPrice(),
            'url_img'       => $this->getUrlImg(),
            'available'     => $this->isAvailable(),
            'created_at'    => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at'    => $this->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
