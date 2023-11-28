<?php

namespace App\Entity;

use App\Repository\NumberRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NumberRepository::class)]
class Number
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $numberId = null;

    #[ORM\Column(length: 255)]
    private ?string $numberNumber = null;


    #[ORM\Column(length: 255)]
    private ?string $airtimeBalance = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $customerName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumberId(): ?int
    {
        return $this->numberId;
    }

    public function setNumberId(int $numberId): static
    {
        $this->numberId = $numberId;

        return $this;
    }

    public function getNumberNumber(): ?string
    {
        return $this->numberNumber;
    }

    public function setNumberNumber(string $numberNumber): static
    {
        $this->numberNumber = $numberNumber;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): static
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getAirtimeBalance(): ?string
    {
        return $this->airtimeBalance;
    }

    public function setAirtimeBalance(string $airtimeBalance): static
    {
        $this->airtimeBalance = $airtimeBalance;

        return $this;
    }
}
