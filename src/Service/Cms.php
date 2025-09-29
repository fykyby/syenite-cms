<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Yaml\Yaml;

class Cms
{
    private static string $theme;
    private static array $config;

    public function __construct()
    {
        // TODO: get theme from db
        self::$theme = 'default';

        $config = $this->readConfig();
        self::$config = $config;
    }

    public function getTheme(): string
    {
        return self::$theme;
    }

    public function setTheme(string $theme): void
    {
        self::$theme = $theme;
    }

    public function getConfig(): array
    {
        return self::$config;
    }

    public function setConfig(array $config): void
    {
        self::$config = $config;
    }

    public function readConfig(): array
    {
        $config = Yaml::parseFile(
            ROOT_DIR . '/cms/themes/' . $this->getTheme() . '/config.yaml',
        );

        return $config;
    }

    public function getBlocksDir(): string
    {
        return ROOT_DIR . '/cms/themes/' . $this->getTheme() . '/blocks';
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
