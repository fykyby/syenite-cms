<?php

namespace App\Entity;

use App\Repository\RedirectRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RedirectRepository::class)]
class Redirect
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[
        Assert\Regex(
            pattern: '/^(\/([a-zA-Z0-9\-_]+|\{[a-zA-Z0-9_]+\})(\/([a-zA-Z0-9\-_]+|\{[a-zA-Z0-9_]+\}))*)|\/$/',
        ),
    ]
    private ?string $fromPath = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[
        Assert\Regex(
            pattern: '/^(\/([a-zA-Z0-9\-_]+|\{[a-zA-Z0-9_]+\})(\/([a-zA-Z0-9\-_]+|\{[a-zA-Z0-9_]+\}))*)|\/$/',
        ),
    ]
    private ?string $toPath = null;

    #[ORM\ManyToOne(inversedBy: 'redirects')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DataLocale $locale = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromPath(): ?string
    {
        return $this->fromPath;
    }

    public function setFromPath(string $fromPath): static
    {
        $this->fromPath = $fromPath;

        return $this;
    }

    public function getToPath(): ?string
    {
        return $this->toPath;
    }

    public function setToPath(string $toPath): static
    {
        $this->toPath = $toPath;

        return $this;
    }

    public function getLocale(): ?DataLocale
    {
        return $this->locale;
    }

    public function setLocale(?DataLocale $locale): static
    {
        $this->locale = $locale;

        return $this;
    }
}
