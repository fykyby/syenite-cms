<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\DataLocale;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function getFunctions(): array
    {
        return [new TwigFunction('__locales', [$this, 'listLocales'])];
    }

    public function listLocales(): array
    {
        return $this->entityManager
            ->getRepository(DataLocale::class)
            ->findAll();
    }
}
