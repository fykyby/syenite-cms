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
    private ?string $email_account_username = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email_account_password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email_account_host = null;

    #[ORM\Column(nullable: true)]
    private ?string $email_account_port = null;

    #[ORM\Column(length: 255)]
    private ?string $current_theme = null;

    #[ORM\Column(nullable: true)]
    private array $layout_data = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmailAccountUsername(): ?string
    {
        return $this->email_account_username;
    }

    public function setEmailAccountUsername(
        ?string $email_account_username,
    ): static {
        $this->email_account_username = $email_account_username;

        return $this;
    }

    public function getEmailAccountPassword(): ?string
    {
        return $this->email_account_password;
    }

    public function setEmailAccountPassword(
        ?string $email_account_password,
    ): static {
        $this->email_account_password = $email_account_password;

        return $this;
    }

    public function getEmailAccountHost(): ?string
    {
        return $this->email_account_host;
    }

    public function setEmailAccountHost(?string $email_account_host): static
    {
        $this->email_account_host = $email_account_host;

        return $this;
    }

    public function getEmailAccountPort(): ?string
    {
        return $this->email_account_port;
    }

    public function setEmailAccountPort(?string $email_account_port): static
    {
        $this->email_account_port = $email_account_port;

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
        return $this->current_theme;
    }

    public function setCurrentTheme(string $current_theme): static
    {
        $this->current_theme = $current_theme;

        return $this;
    }

    public function getLayoutData(): array
    {
        return $this->layout_data;
    }

    public function setLayoutData(array $layout_data): static
    {
        $this->layout_data = $layout_data;

        return $this;
    }
}
