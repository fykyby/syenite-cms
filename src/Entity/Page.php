<?php

namespace App\Entity;

use App\Repository\PageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[UniqueEntity('path')]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 512)]
    #[Assert\Regex(pattern: '/^(\/([a-zA-Z0-9\-_]+|\{[a-zA-Z0-9_]+\})(\/([a-zA-Z0-9\-_]+|\{[a-zA-Z0-9_]+\}))*)|\/$/')]
    private ?string $path = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Choice(options: ['default'])]
    private ?string $type = null;

    #[ORM\Column]
    private array $data = [];

    #[ORM\Column]
    private array $meta = [];

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

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getMetaTitle(): ?string
    {
        return $this->meta_title;
    }

    public function setMetaTitle(string $meta_title): static
    {
        $this->meta_title = $meta_title;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->meta_description;
    }

    public function setMetaDescription(string $meta_description): static
    {
        $this->meta_description = $meta_description;

        return $this;
    }

    public function getMetaRawHtml(): ?string
    {
        return $this->meta_raw_html;
    }

    public function setMetaRawHtml(string $meta_raw_html): static
    {
        $this->meta_raw_html = $meta_raw_html;

        return $this;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function setMeta(array $meta): static
    {
        $this->meta = $meta;

        return $this;
    }
}
