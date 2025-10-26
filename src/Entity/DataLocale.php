<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DataLocaleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DataLocaleRepository::class)]
class DataLocale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    #[Assert\Length(max: 255)]
    private ?string $domain = null;

    #[ORM\Column]
    private ?bool $isDefault = null;

    /**
     * @var Collection<int, Page>
     */
    #[
        ORM\OneToMany(
            targetEntity: Page::class,
            mappedBy: 'locale',
            orphanRemoval: true,
        ),
    ]
    private Collection $pages;

    /**
     * @var Collection<int, LayoutData>
     */
    #[
        ORM\OneToMany(
            targetEntity: LayoutData::class,
            mappedBy: 'locale',
            orphanRemoval: true,
        ),
    ]
    private Collection $layouts;

    /**
     * @var Collection<int, Redirect>
     */
    #[ORM\OneToMany(targetEntity: Redirect::class, mappedBy: 'locale', orphanRemoval: true)]
    private Collection $redirects;

    public function __construct()
    {
        $this->pages = new ArrayCollection();
        $this->layouts = new ArrayCollection();
        $this->redirects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): static
    {
        $domain === '' ? ($this->domain = null) : ($this->domain = $domain);

        return $this;
    }

    /**
     * @return Collection<int, Page>
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(Page $page): static
    {
        if (!$this->pages->contains($page)) {
            $this->pages->add($page);
            $page->setLocale($this);
        }

        return $this;
    }

    public function removePage(Page $page): static
    {
        if ($this->pages->removeElement($page)) {
            // set the owning side to null (unless already changed)
            if ($page->getLocale() === $this) {
                $page->setLocale(null);
            }
        }

        return $this;
    }

    public function isDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): static
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * @return Collection<int, LayoutData>
     */
    public function getLayouts(): Collection
    {
        return $this->layouts;
    }

    public function addLayout(LayoutData $layout): static
    {
        if (!$this->layouts->contains($layout)) {
            $this->layouts->add($layout);
            $layout->setLocale($this);
        }

        return $this;
    }

    public function removeLayout(LayoutData $layout): static
    {
        if ($this->layouts->removeElement($layout)) {
            // set the owning side to null (unless already changed)
            if ($layout->getLocale() === $this) {
                $layout->setLocale(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Redirect>
     */
    public function getRedirects(): Collection
    {
        return $this->redirects;
    }

    public function addRedirect(Redirect $redirect): static
    {
        if (!$this->redirects->contains($redirect)) {
            $this->redirects->add($redirect);
            $redirect->setLocale($this);
        }

        return $this;
    }

    public function removeRedirect(Redirect $redirect): static
    {
        if ($this->redirects->removeElement($redirect)) {
            // set the owning side to null (unless already changed)
            if ($redirect->getLocale() === $this) {
                $redirect->setLocale(null);
            }
        }

        return $this;
    }
}
