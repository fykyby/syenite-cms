<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SettingsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SettingsRepository::class)]
class Settings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email]
    private ?string $emailAccountUsername = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailAccountPassword = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailAccountHost = null;

    #[ORM\Column(nullable: true)]
    private ?string $emailAccountPort = null;

    #[ORM\Column(length: 255)]
    private ?string $currentTheme = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmailAccountUsername(): ?string
    {
        return $this->emailAccountUsername;
    }

    public function setEmailAccountUsername(
        ?string $emailAccountUsername,
    ): static {
        $this->emailAccountUsername = $emailAccountUsername;

        return $this;
    }

    public function getEmailAccountPassword(): ?string
    {
        return $this->emailAccountPassword;
    }

    public function setEmailAccountPassword(
        ?string $emailAccountPassword,
    ): static {
        $this->emailAccountPassword = $emailAccountPassword;

        return $this;
    }

    public function getEmailAccountHost(): ?string
    {
        return $this->emailAccountHost;
    }

    public function setEmailAccountHost(?string $emailAccountHost): static
    {
        $this->emailAccountHost = $emailAccountHost;

        return $this;
    }

    public function getEmailAccountPort(): ?string
    {
        return $this->emailAccountPort;
    }

    public function setEmailAccountPort(?string $emailAccountPort): static
    {
        $this->emailAccountPort = $emailAccountPort;

        return $this;
    }

    public function getEmailSettings(): array
    {
        return [
            'username' => $this->getEmailAccountUsername(),
            'password' => $this->getEmailAccountPassword(),
            'host' => $this->getEmailAccountHost(),
            'port' => $this->getEmailAccountPort(),
        ];
    }

    public function getCurrentTheme(): ?string
    {
        return $this->currentTheme;
    }

    public function setCurrentTheme(string $currentTheme): static
    {
        $this->currentTheme = $currentTheme;

        return $this;
    }
}
