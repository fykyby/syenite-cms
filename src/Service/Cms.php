<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Yaml\Yaml;

class Cms
{
    private static string $themeName;

    public function __construct()
    {
        // TODO: get theme from db
        self::$themeName = 'Default';
    }

    public function getThemeName(): string
    {
        return self::$themeName;
    }

    public function setThemeName(string $theme): void
    {
        self::$themeName = $theme;
    }

    public function getBlocksDir(): string
    {
        return ROOT_DIR . "/Themes/{$this->getThemeName()}/Blocks";
    }

    public function listBlocks(): array
    {
        $dir = scandir($this->getBlocksDir());
        array_splice($dir, 0, 2);
        return $dir;
    }

    public function getBlockSchema(string $blockName): array
    {
        $block = Yaml::parseFile(
            "{$this->getBlocksDir()}/{$blockName}/schema.yaml",
        );
        return $block;
    }

    public function getBlockTemplatePath(string $blockName): string
    {
        return "@Themes/{$this->getThemeName()}/Blocks/{$blockName}/view.twig";
    }

    public function getLayoutTemplatePath(string $layoutName): string
    {
        return "@Themes/{$this->getThemeName()}/Layouts/{$layoutName}/view.twig";
    }

    public function getLayoutSchema(string $layoutName): array
    {
        $layout = Yaml::parseFile(
            "{$this->getBlocksDir()}/{$layoutName}/view.yaml",
        );
        return $layout;
    }
}
