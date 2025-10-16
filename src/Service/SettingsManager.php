<?php

declare(strict_types=1);

namespace App\Service;

use Exception;
use Symfony\Component\Yaml\Yaml;

class SettingsManager
{
    private array $settings;

    public function __construct()
    {
        $this->settings = $this->read();
    }

    private function read(): array
    {
        $settings = [];
        try {
            $settings = Yaml::parseFile(ROOT_DIR . $_ENV['CMS_SETTINGS_PATH']);
        } catch (Exception) {
        }
        return $settings ?? [];
    }

    private function write(): void
    {
        $yaml = Yaml::dump($this->settings);
        file_put_contents(ROOT_DIR . $_ENV['CMS_SETTINGS_PATH'], $yaml);
    }

    public function setValue(string $key, mixed $value): void
    {
        $this->settings[$key] = $value;
        $this->write();
    }

    public function getValue(string $key): mixed
    {
        return $this->settings[$key];
    }

    public function get(): array
    {
        return $this->settings;
    }

    public function set(array $settings): void
    {
        $this->settings = $settings;
        $this->write();
    }
}
