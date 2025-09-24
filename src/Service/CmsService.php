<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Yaml\Yaml;

class CmsService
{
    private static string $theme;

    public static function init(): void
    {
        self::$theme = 'default';
    }

    public function getTheme(): string
    {
        return self::$theme;
    }

    public function setTheme(string $theme): void
    {
        self::$theme = $theme;
    }

    public function getBlocksDir(): string
    {
        return dirname(dirname(dirname(__FILE__))) .
            '/cms/themes/' .
            $this->getTheme() .
            '/blocks';
    }

    public function getBlockTemplatePath(string $blockName): string
    {
        return '@themes/' .
            $this->getTheme() .
            '/blocks/' .
            $blockName .
            '/' .
            $blockName .
            '.twig';
    }

    public function getLayoutTemplatePath(string $layoutName): string
    {
        return '@themes/' .
            $this->getTheme() .
            '/layouts/' .
            $layoutName .
            '.twig';
    }

    public function listBlocks(): array
    {
        $dir = scandir($this->getBlocksDir());
        array_splice($dir, 0, 2);
        return $dir;
    }

    public function getBlockData(string $blockName): array
    {
        $block = Yaml::parseFile(
            $this->getBlocksDir() .
                '/' .
                $blockName .
                '/' .
                $blockName .
                '.yaml',
        );
        return $block;
    }
}

CmsService::init();
