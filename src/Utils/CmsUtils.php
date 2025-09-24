<?php

declare(strict_types=1);

namespace App\Utils;

use Symfony\Component\Yaml\Yaml;

class CmsUtils
{
    private static string $theme;

    public static function getTheme(): string
    {
        return self::$theme;
    }

    public static function setTheme(string $theme): void
    {
        self::$theme = $theme;
    }

    public static function getBlocksDir(): string
    {
        return dirname(dirname(dirname(__FILE__))) .
            '/cms/themes/' .
            self::$theme .
            '/blocks';
    }

    public static function getBlockTemplatePath(string $blockName): string
    {
        return '@themes/' .
            self::$theme .
            '/blocks/' .
            $blockName .
            '/' .
            $blockName .
            '.twig';
    }

    public static function getLayoutTemplatePath(string $layoutName): string
    {
        return '@themes/' . self::$theme . '/layouts/' . $layoutName . '.twig';
    }

    public static function listBlocks(): array
    {
        $dir = scandir(self::getBlocksDir());
        array_splice($dir, 0, 2);
        return $dir;
    }

    public static function getBlockData(string $blockName): array
    {
        $block = Yaml::parseFile(
            self::getBlocksDir() .
                '/' .
                $blockName .
                '/' .
                $blockName .
                '.yaml',
        );
        return $block;
    }
}
