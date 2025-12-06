<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\DataLocale;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
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
            new TwigFunction('post_max_size', [$this, 'postMaxSize']),
            new TwigFunction('upload_max_filesize', [
                $this,
                'uploadMaxFilesize',
            ]),
        ];
    }

    public function getFilters(): array
    {
        return [new TwigFilter('format_bytes', [$this, 'formatBytes'])];
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

    public function postMaxSize(): int
    {
        return $this->convertToBytes(ini_get('post_max_size'));
    }

    public function uploadMaxFilesize(): int
    {
        return $this->convertToBytes(ini_get('upload_max_filesize'));
    }

    public function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes < 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $factor = floor((strlen((string) $bytes) - 1) / 3);
        $factor = min($factor, count($units) - 1);

        $formatted = $bytes / pow(1024, $factor);

        return round($formatted, $precision) . ' ' . $units[$factor];
    }

    private function convertToBytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);

        switch ($last) {
            case 'g':
                return (int) $val * 1024 * 1024 * 1024;
            case 'm':
                return (int) $val * 1024 * 1024;
            case 'k':
                return (int) $val * 1024;
            default:
                return (int) $val;
        }
    }
}
