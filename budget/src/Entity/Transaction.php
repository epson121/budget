<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TransactionRepository;
use Maldoinc\Doctrine\Filter\Provider\PresetFilterProvider;
use Maldoinc\Doctrine\Filter\Annotation\Expose as FilterExpose;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{

    const TYPE_EXPENSE = 'expense';
    const TYPE_DEPOSIT = 'deposit';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    #[FilterExpose(operators: [PresetFilterProvider::EQ], serializedName: 'user_id')]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[FilterExpose(operators: [PresetFilterProvider::EQ], serializedName: 'category_id')]
    private ?Category $category = null;

    #[ORM\Column]
    #[FilterExpose(operators: [PresetFilterProvider::EQ, PresetFilterProvider::GT, PresetFilterProvider::LT])]
    private ?float $amount = null;

    #[ORM\Column]
    #[FilterExpose(operators: [PresetFilterProvider::GT, PresetFilterProvider::LT, PresetFilterProvider::EQ])]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
