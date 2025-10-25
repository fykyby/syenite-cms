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
        return [
            new TwigFunction('__locales', [$this, 'listLocales']),
            new TwigFunction('env', [$this, 'env']),
        ];
    }

    public function listLocales(): array
    {
        return $this->entityManager
            ->getRepository(DataLocale::class)
            ->findAll();
    }

    public function env(string $key): ?string
    {
        return $_ENV[$key];
    }
}
