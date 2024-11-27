<?php

namespace App\Entity;

use App\Repository\NumberRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NumberRepository::class)]
class Number
{
    public const MSISDN="msisdn";

    public const AMOUNT="amount";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $numberid = null;

    #[ORM\Column(length: 255)]
    private ?string $customername = null;

    #[ORM\Column(length: 255)]
    private ?string $numberphone = null;

    #[ORM\Column(length: 255)]
    private ?string $msisdn = null;


    #[ORM\Column(length: 255)]
    private ?string $accountNumber = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $numberoldbalance = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $numberbalance = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $numbernewbalance = null;


    #[ORM\Column(length: 255,nullable: true)]
    private ?string $numberdatabalance = null;


    #[ORM\Column(length: 255,nullable: true)]
    private ?string $numberdataoldbalance = null;

    #[ORM\Column(type: Types::BOOLEAN,nullable: true)]
    private ?bool $isMoney = null;


    #[ORM\Column(length: 255,nullable: true)]
    private ?string $numberdatanewbalance = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumberid(): ?string
    {
        return $this->numberid;
    }

    public function setNumberid(string $numberid): static
    {
        $this->numberid = $numberid;

        return $this;
    }


    public function getCustomername(): ?string
    {
        return $this->customername;
    }

    public function setCustomername(string $numberid): static
    {
        $this->customername = $numberid;

        return $this;
    }


    /**
     * @return string|null
     */
    public function getNumberphone(): ?string
    {
        return $this->numberphone;
    }


    /**
     * @param string|null $numberphone
     */
    public function setNumberphone(?string $numberphone): static
    {
        $this->numberphone = $numberphone;

        return $this;
    }

    public function getNumberoldbalance(): ?string
    {
        return $this->numberoldbalance;
    }

    public function setNumberoldbalance(string $numberoldbalance): static
    {
        $this->numberoldbalance = $numberoldbalance;

        return $this;
    }

    public function getNumbernewbalance(): ?string
    {
        return $this->numbernewbalance;
    }

    public function setNumbernewbalance(string $numbernewbalance): static
    {
        $this->numbernewbalance = $numbernewbalance;

        return $this;
    }

    public function getMsisdn(): ?string
    {
        return $this->msisdn;
    }

    public function setMsisdn(string $msisdn): static
    {
        $this->msisdn = $msisdn;

        return $this;
    }


    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountnummber): static
    {
        $this->accountNumber = $accountnummber;

        return $this;
    }

    public function getNumberdatabalance(): ?string
    {
        return $this->numberdatabalance;
    }

    public function setNumberdatabalance(string $numberdatabalance): static
    {
        $this->numberdatabalance = $numberdatabalance;

        return $this;
    }

    public function getNumberbalance(): ?string
    {
        return $this->numberbalance;
    }

    public function setNumberbalance(?string $numberbalance): static
    {
        $this->numberbalance = $numberbalance;

        return $this;
    }

    public function getNumberdataoldbalance(): ?string
    {
        return $this->numberdataoldbalance;
    }

    public function setNumberdataoldbalance(?string $numberdataoldbalance): static
    {
        $this->numberdataoldbalance = $numberdataoldbalance;

        return $this;
    }

    public function getNumberdatanewbalance(): ?string
    {
        return $this->numberdatanewbalance;
    }

    public function setNumberdatanewbalance(?string $numberdatanewbalance): static
    {
        $this->numberdatanewbalance = $numberdatanewbalance;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsMoney(): ?bool
    {
        return $this->isMoney;
    }

    /**
     * @param bool|null $isMoney
     */
    public function setIsMoney(?bool $isMoney): void
    {
        $this->isMoney = $isMoney;
    }
}
