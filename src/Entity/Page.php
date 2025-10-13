<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[
    ORM\Table(
        name: 'page',
        uniqueConstraints: [
            new ORM\UniqueConstraint(
                name: 'unique_path_per_locale',
                columns: ['path', 'locale'],
            ),
        ],
    ),
]
#[
    UniqueEntity(
        fields: ['path', 'locale'],
        message: 'This path already exists for this locale',
    ),
]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 512)]
    #[
        Assert\Regex(
            pattern: '/^(\/([a-zA-Z0-9\-_]+|\{[a-zA-Z0-9_]+\})(\/([a-zA-Z0-9\-_]+|\{[a-zA-Z0-9_]+\}))*)|\/$/',
        ),
    ]
    private ?string $path = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Choice(options: ['default'])]
    private ?string $type = null;

    #[ORM\Column]
    private array $data = [];

    #[ORM\Column]
    private array $meta = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $layoutName = null;

    #[ORM\ManyToOne(inversedBy: 'pages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DataLocale $locale = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<int,mixed> $data
     */
    public function setData(array $data): Page
    {
        $this->data = $data;

        return $this;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @param array<int,mixed> $meta
     */
    public function setMeta(array $meta): Page
    {
        $this->meta = $meta;

        return $this;
    }

    public function getLayoutName(): ?string
    {
        return $this->layoutName;
    }

    public function setLayoutName(?string $layoutName): static
    {
        $this->layoutName = $layoutName;

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
