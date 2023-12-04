<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    public const TYPE="type";
    public const EXTREFNUM = "extrefnum";
    public const MSISDN = "msisdn";
    public const TRANSACTION_ID="transactionid";
    public const MESSAGE="message";

    public const DATE="date";

    public const TXNSTATUS="txnstatus";

    public const TXNID="txnid";
    public const PIN="pin";
    public const LANGUAGE1="language1";
    public const EXTNWCODE="extnwcode";


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $transactionid = null;

    #[ORM\Column(type: "bigint",nullable: true)]
    private ?int $txnStatus = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $txnid = null;


    #[ORM\Column(length: 255,nullable: true)]
    private ?string $msisdn = null;


    #[ORM\Column(length: 255,nullable: true)]
    private ?string $msisdn2 = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $extrefnum = null;

    #[ORM\Column(length: 500,nullable: true)]
    private ?string $message = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateTransaction = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateEndTransaction = null;

    private ?string $date = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type:Types::FLOAT,nullable: true)]
    private ?float $balance = null;

    #[ORM\Column(type:Types::FLOAT,nullable: true)]
    private ?float $balanceold = null;

    #[ORM\Column(type:Types::FLOAT,nullable: true)]
    private ?float $balancenew =  null;


    #[ORM\Column(length: 255,nullable: true)]
    private ?string $language1 = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $language2 = null;

    #[ORM\Column(length: 255)]
    private ?string $pin = null;

    #[ORM\Column(length: 255)]
    private ?string $extnwcode = null;

    #[ORM\Column(length: 255)]
    private ?string $selector = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransactionid(): ?string
    {
        return $this->transactionid;
    }

    public function setTransactionid(string $transactionid): static
    {
        $this->transactionid = $transactionid;

        return $this;
    }

    public function getTxnStatus(): ?string
    {
        return $this->txnStatus;
    }

    public function setTxnStatus(string $txnStatus): static
    {
        $this->txnStatus = $txnStatus;

        return $this;
    }

    public function getTxnid(): ?string
    {
        return $this->txnid;
    }

    public function setTxnid(?string $txnid): static
    {
        $this->txnid = $txnid;

        return $this;
    }

    public function getExtrefnum(): ?string
    {
        return $this->extrefnum;
    }

    public function setExtrefnum(?string $extrefnum): static
    {
        $this->extrefnum = $extrefnum;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    public function getBalanceold(): ?float
    {
        return $this->balanceold;
    }

    public function setBalanceold(float $balanceold): static
    {
        $this->balanceold = $balanceold;

        return $this;
    }

    public function getBalancenew(): ?float
    {
        return $this->balancenew;
    }

    public function setBalancenew(float $balancenew): static
    {
        $this->balancenew = $balancenew;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDateTransaction(): ?\DateTimeInterface
    {
        return $this->dateTransaction;
    }


    /**
     * @param \DateTimeInterface|null $dateTransaction
     */
    public function setDateTransaction(?\DateTimeInterface $dateTransaction): static
    {
        $this->dateTransaction = $dateTransaction;
        return $this;
    }


    /**
     * @return string|null
     */
    public function getDate(): ?string
    {
        return $this->date;
    }


    /**
     * @param string|null $date
     */
    public function setDate(?string $date): void
    {
        $this->date = $date;
    }

    public function getLanguage1(): ?string
    {
        return $this->language1;
    }

    public function setLanguage1(?string $language1): static
    {
        $this->language1 = $language1;

        return $this;
    }

    public function getLanguage2(): ?string
    {
        return $this->language2;
    }

    public function setLanguage2(?string $language2): static
    {
        $this->language2 = $language2;

        return $this;
    }

    public function getDateEndTransaction(): ?\DateTimeInterface
    {
        return $this->dateEndTransaction;
    }

    public function setDateEndTransaction(\DateTimeInterface $dateEndTransaction): static
    {
        $this->dateEndTransaction = $dateEndTransaction;

        return $this;
    }

    public function getPin(): ?string
    {
        return $this->pin;
    }

    public function setPin(string $pin): static
    {
        $this->pin = $pin;

        return $this;
    }

    public function getExtnwcode(): ?string
    {
        return $this->extnwcode;
    }

    public function setExtnwcode(string $extnwcode): static
    {
        $this->extnwcode = $extnwcode;

        return $this;
    }

    public function getSelector(): ?string
    {
        return $this->selector;
    }

    public function setSelector(string $selector): static
    {
        $this->selector = $selector;

        return $this;
    }

    public function getMsisdn(): ?string
    {
        return $this->msisdn;
    }

    public function setMsisdn(?string $msisdn): static
    {
        $this->msisdn = $msisdn;

        return $this;
    }

    public function getMsisdn2(): ?string
    {
        return $this->msisdn2;
    }

    public function setMsisdn2(?string $msisdn2): static
    {
        $this->msisdn2 = $msisdn2;

        return $this;
    }

}
