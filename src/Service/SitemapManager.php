<?php

namespace App\Service;

use App\Entity\DataLocale;
use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;

class SitemapManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Cms $cms,
    ) {}

    public function getFilePath(mixed $localeId): string
    {
        return ROOT_DIR . "/public/sitemap/{$localeId}.xml";
    }

    public function getWebPath(mixed $localeId): string
    {
        return "/sitemap/{$localeId}.xml";
    }

    public function generate(mixed $localeId): void
    {
        $locale = $this->entityManager
            ->getRepository(DataLocale::class)
            ->find($localeId);

        $domain = $locale->getDomain() ?? 'localhost';

        $pages = $this->fetchPages($locale);
        $xml = $this->buildXml($pages, $domain);

        $path = $this->getFilePath($locale->getId());
        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($path, $xml);
    }

    public function delete(mixed $localeId): void
    {
        $path = $this->getFilePath($localeId);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    private function fetchPages(DataLocale $locale): array
    {
        return $this->entityManager->getRepository(Page::class)->findBy([
            'locale' => $locale,
        ]);
    }

    /**
     * @param array<Page> $pages
     */
    private function buildXml(array $pages, string $domain): string
    {
        $baseUrl = "https://{$domain}";

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .=
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' .
            PHP_EOL;

        foreach ($pages as $page) {
            $path = ltrim($page->getPath(), '/');
            $url = "{$baseUrl}/{$path}";

            $xml .= '  <url>' . PHP_EOL;
            $xml .=
                '    <loc>' .
                htmlspecialchars($url, ENT_XML1) .
                '</loc>' .
                PHP_EOL;

            $updatedAt = $page->getUpdatedAt();
            $xml .=
                '    <lastmod>' .
                $updatedAt->format('Y-m-d\TH:i:sP') .
                '</lastmod>' .
                PHP_EOL;

            $priority = $this->calculatePriorityByDepth($path);
            $xml .=
                '    <priority>' .
                number_format($priority, 1) .
                '</priority>' .
                PHP_EOL;

            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';

        return $xml;
    }

    private function calculatePriorityByDepth(string $path): float
    {
        // Count slashes to determine depth
        $depth = 0;
        if ($path !== '') {
            $depth = substr_count(trim($path, '/'), '/') + 1;
        }

        // Priority decreases with depth
        return match ($depth) {
            0 => 1,
            1 => 0.8,
            2 => 0.6,
            default => 0.4,
        };
    }
}
