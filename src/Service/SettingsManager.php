<?php

declare(strict_types=1);

namespace App\Service;

use Exception;

class SettingsManager
{
    public static string $currentThemeKey = 'currentTheme';
    public static string $emailAccountKey = 'emailAccount';

    private array $settings;

    public function __construct()
    {
        $this->settings = $this->read();
    }

    private function read(): array
    {
        $settings = [];
        try {
            $raw = file_get_contents(ROOT_DIR . $_ENV['CMS_SETTINGS_PATH']);
            if (!$raw) {
                $raw = '';
            }

            $settings = json_decode($raw, true);
        } catch (Exception) {
        }
        return $settings ?? [];
    }

    private function write(): void
    {
        $json = json_encode($this->settings);
        file_put_contents(ROOT_DIR . $_ENV['CMS_SETTINGS_PATH'], $json);
    }

    public function setValue(string $key, mixed $value): void
    {
        $this->settings[$key] = $value;
        $this->write();
    }

    public function getValue(string $key): mixed
    {
        return $this->settings[$key] ?? null;
    }

    public function get(): array
    {
        return $this->settings;
    }

    /** Should only be used with modified array received from get() method */
    public function set(array $settings): void
    {
        $this->settings = $settings;
        $this->write();
    }
}
