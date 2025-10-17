<?php

declare(strict_types=1);

namespace App\Service;

class Cms
{
    private static string $themeName;

    public function __construct(SettingsManager $settingsManager)
    {
        $settings = $settingsManager->get();

        $themeName = $settings[SettingsManager::$currentThemeKey] ?? null;
        if (!$themeName) {
            $themes = $this->listThemes();
            if (count($themes) === 0) {
                throw new \Exception('No themes found');
            }

            $themeName = $themes[0];
        }

        self::$themeName = $themeName;
    }

    public function getThemeName(): string
    {
        return self::$themeName;
    }

    public function setThemeName(string $theme): void
    {
        self::$themeName = $theme;
    }

    public function getStaticDir(): string
    {
        return ROOT_DIR . "/Themes/{$this->getThemeName()}/Static";
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
            $raw = file_get_contents(
                "{$this->getBlocksDir()}/{$blockName}/schema.json",
            );
            $block = json_decode($raw, true);
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
            $raw = file_get_contents(
                "{$this->getLayoutsDir()}/{$layoutName}/schema.json",
            );
            $layout = json_decode($raw, true);
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
