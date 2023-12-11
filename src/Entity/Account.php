<?php

namespace App\Entity;

use App\Repository\AccountRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
class Account  implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ACCOUNT_MSISDN="msisdn";
    public const API_KEY="apikey";
    public const LOGIN="login";
    public const SOURCETYPE="sourcetype";
    public const PASSWORD="password";
    public const PIN="pin";
    public const SERVICEPORT="serviceport";
    public const REQUESTGATEWAYCODE="requestgatewaycode";
    public const REQUESTGATEWAYTYPE="requestgatewaytype";
    public const USERNAME="username";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(type:"bigint")]
    private ?int $accountId = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;


    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $msisdn = null;


    #[ORM\Column(length: 255)]
    private ?string $currency = null;

    #[ORM\Column(length: 255)]
    private ?string $accountNumber = null;


    #[ORM\Column(nullable: true)]
    private ?float $balance = null;


    #[ORM\Column(nullable: true)]
    private ?float $newbalance = null;


    #[ORM\Column(nullable: true)]
    private ?float $oldbalance = null;

    #[ORM\Column(length: 255)]
    private ?string $apikey = null;

    #[ORM\Column(length: 255)]
    private ?string $selector = null;

    #[ORM\Column(length: 255)]
    private ?string $extnwcode = null;

    #[ORM\Column(length: 255)]
    private ?string $pin = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $msisdn2 = null;

    #[ORM\Column(length: 255)]
    private ?string $language1 = null;

    #[ORM\Column(length: 255)]
    private ?string $language2 = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $login = null;

    #[ORM\Column(length: 255)]
    private ?string $sourcetype = null;

    #[ORM\Column(length: 255)]
    private ?string $serviceport = null;

    #[ORM\Column(length: 255)]
    private ?string $requestgatewaytype = null;

    #[ORM\Column(length: 255)]
    private ?string $requestgatewaycode = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }


    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): static
    {
        $this->password = $password;
        return $this;
    }


    public function getPassword(): ?string
    {
       return  $this->password;
    }


    /**
     * @return int|null
     */
    public function getAccountId(): ?int
    {
        return $this->accountId;
    }

    /**
     * @param int|null $accountId
     */
    public function setAccountId(?int $accountId): void
    {
        $this->accountId = $accountId;
    }

    public function getRoles(): array
    {
      return [];
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * @return float|null
     */
    public function getBalance(): ?float
    {
        return $this->balance;
    }


    /**
     * @param float|null $balance
     */
    public function setBalance(?float $balance): void
    {
        $this->balance = $balance;
    }

    public function getApikey(): ?string
    {
        return $this->apikey;
    }

    public function setApikey(string $apikey): static
    {
        $this->apikey = $apikey;

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

    public function getNewbalance(): ?float
    {
        return $this->newbalance;
    }

    public function setNewbalance(?float $newbalance): static
    {
        $this->newbalance = $newbalance;

        return $this;
    }

    public function getOldbalance(): ?float
    {
        return $this->oldbalance;
    }

    public function setOldbalance(?float $oldbalance): static
    {
        $this->oldbalance = $oldbalance;

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

    public function getExtnwcode(): ?string
    {
        return $this->extnwcode;
    }

    public function setExtnwcode(string $extnwcode): static
    {
        $this->extnwcode = $extnwcode;

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

    public function getMsisdn2(): ?string
    {
        return $this->msisdn2;
    }

    public function setMsisdn2(string $msisdn2): static
    {
        $this->msisdn2 = $msisdn2;

        return $this;
    }

    public function getLanguage1(): ?string
    {
        return $this->language1;
    }

    public function setLanguage1(string $language1): static
    {
        $this->language1 = $language1;

        return $this;
    }

    public function getLanguage2(): ?string
    {
        return $this->language2;
    }

    public function setLanguage2(string $language2): static
    {
        $this->language2 = $language2;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;

        return $this;
    }

    public function getSourcetype(): ?string
    {
        return $this->sourcetype;
    }

    public function setSourcetype(string $sourcetype): static
    {
        $this->sourcetype = $sourcetype;

        return $this;
    }

    public function getServiceport(): ?string
    {
        return $this->serviceport;
    }

    public function setServiceport(string $serviceport): static
    {
        $this->serviceport = $serviceport;

        return $this;
    }

    public function getRequestgatewaytype(): ?string
    {
        return $this->requestgatewaytype;
    }

    public function setRequestgatewaytype(string $requestgatewaytype): static
    {
        $this->requestgatewaytype = $requestgatewaytype;

        return $this;
    }

    public function getRequestgatewaycode(): ?string
    {
        return $this->requestgatewaycode;
    }

    public function setRequestgatewaycode(string $requestgatewaycode): static
    {
        $this->requestgatewaycode = $requestgatewaycode;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

}
