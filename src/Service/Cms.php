<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Settings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Yaml\Yaml;

class Cms
{
    private static string $themeName;

    public function __construct(private EntityManagerInterface $entityManager)
    {
        $settings = $entityManager->getRepository(Settings::class)->find(1);

        if ($settings !== null) {
            self::$themeName = $settings->getCurrentTheme();
        }
    }

    public function getThemeName(): string
    {
        return self::$themeName;
    }

    public function setThemeName(string $theme): void
    {
        self::$themeName = $theme;
    }

    public function getLayoutsDir(): string
    {
        return ROOT_DIR . "/Themes/{$this->getThemeName()}/Layouts";
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

    public function getBlockSchema(string $blockName): ?array
    {
        try {
            $block = Yaml::parseFile(
                "{$this->getBlocksDir()}/{$blockName}/schema.yaml",
            );
            return $block;
        } catch (\Exception) {
            return null;
        }
    }

    public function getThemesDir(): string
    {
        return ROOT_DIR . '/Themes';
    }

    public function listThemes(): array
    {
        $dir = scandir($this->getThemesDir());
        array_splice($dir, 0, 2);
        return $dir;
    }

    public function getLayoutSchema(string $layoutName): ?array
    {
        try {
            $layout = Yaml::parseFile(
                "{$this->getLayoutsDir()}/{$layoutName}/schema.yaml",
            );
            return $layout;
        } catch (\Exception) {
            return null;
        }
    }

    public function listLayouts(): array
    {
        $dir = scandir($this->getLayoutsDir());
        array_splice($dir, 0, 2);
        return $dir;
    }

    public function getBlockTemplatePath(string $blockName): string
    {
        return "@Themes/{$this->getThemeName()}/Blocks/{$blockName}/view.twig";
    }

    public function getLayoutTemplatePath(string $layoutName): string
    {
        return "@Themes/{$this->getThemeName()}/Layouts/{$layoutName}/view.twig";
    }
}
