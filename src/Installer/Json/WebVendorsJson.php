<?php

namespace Architekt\Installer\Json;

class WebVendorsJson
{
    private string $path;
    private array $content;

    private function __construct(
        string $path
    )
    {
        $this->path = $path;
        $this->content = $this->read();
    }

    public function list(): array
    {
        return array_keys($this->content ?? []);
    }

    public function webVendor(string $name): ?array
    {
        return $this->content[$name] ?? null;
    }

    public function javascripts(string $name): array
    {
        $jsFiles = $this->webVendor($name)['js'] ?? null;
        if (!$jsFiles) {
            return [];
        }
        if (!is_array($jsFiles)) {
            $jsFiles = [$jsFiles];
        }

        return $jsFiles;
    }

    public function styleSheets(string $name): array
    {
        $cssFiles = $this->webVendor($name)['css'] ?? null;
        if (!$cssFiles) {
            return [];
        }
        if (!is_array($cssFiles)) {
            return [$cssFiles];
        }

        return $cssFiles;
    }

    public function otherFiles(string $name): array
    {
        $otherFiles = $this->webVendor($name)['other'] ?? null;
        if (!$otherFiles) {
            return [];
        }
        if (!is_array($otherFiles)) {
            return [$otherFiles];
        }

        return $otherFiles;
    }


    public function addPrerequisites(array $webVendors): array
    {
        $requiredWebVendors = [];
        foreach ($webVendors as $webVendor) {
            $prerequisites = $this->webVendor($webVendor)['require'] ?? [];

            if (!is_array($prerequisites)) {
                $prerequisites = [$prerequisites];
            }

            $requiredWebVendors = array_merge($this->addPrerequisites($prerequisites), $prerequisites, $requiredWebVendors);
        }

        return array_unique(array_merge($requiredWebVendors,$webVendors));
    }

    /** @return string[] */
    public function prerequisites(string $webVendor): array
    {
        return $this->webVendor($webVendor)['require'] ?? [];
    }


    public static function init(string $path): static
    {
        return new self($path);
    }


    public function get(
        string $path): static
    {
        return new self(
            $path
        );
    }

    public function read(): array
    {
        if (!file_exists($file = $this->path . DIRECTORY_SEPARATOR . 'libraries.json')) {
            return [];
        }

        return json_decode(file_get_contents($file), true);
    }
}