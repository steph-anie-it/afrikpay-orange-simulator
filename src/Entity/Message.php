<?php

namespace App\Entity;

use App\Repository\LanguageRepository;
use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{

    public const TYPE="type";
    public const MESSAGEINDEX="languageindex";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $languageid = null;


    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $languagename = null;

    #[ORM\Column(length: 255)]
    private ?string $languageslug = null;

    #[ORM\Column(type:"bigint")]
    private ?int $languageindex = null;

    #[ORM\Column(length: 255)]
    private ?string $message = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLanguageid(): ?string
    {
        return $this->languageid;
    }

    public function setLanguageid(string $languageid): static
    {
        $this->languageid = $languageid;

        return $this;
    }

    public function getLanguagename(): ?string
    {
        return $this->languagename;
    }

    public function setLanguagename(string $languagename): static
    {
        $this->languagename = $languagename;

        return $this;
    }

    public function getLanguageslug(): ?string
    {
        return $this->languageslug;
    }

    public function setLanguageslug(string $languageslug): static
    {
        $this->languageslug = $languageslug;

        return $this;
    }

  
    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }


    /**
     * @return int|null
     */
    public function getLanguageindex(): ?int
    {
        return $this->languageindex;
    }


    /**
     * @param int|null $languageindex
     */
    public function setLanguageindex(?int $languageindex): static
    {
        $this->languageindex = $languageindex;

        return  $this;
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


}
