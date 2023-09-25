<?php

namespace Architekt\Installer\Json;

use stdClass;

class ThemesJson
{
    private string $path;
    private array $content;

    private function __construct(
        string $path
    ){
        $this->path = $path;
        $this->content = $this->read();
    }

    public function themes(): array
    {
        return array_keys($this->content ?? []);
    }

    public function theme(string $name): ?array
    {
        return $this->content[$name] ?? null;
    }

    public function directoryImages(string $name): ?string
    {
        return $this->theme($name)['images'] ?? null;
    }

    public function javascripts(string $name): array
    {
        $jsFiles = $this->theme($name)['js'] ?? null;
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
        $cssFiles = $this->theme($name)['css'] ?? null;
        if (!$cssFiles) {
            return [];
        }
        if (!is_array($cssFiles)) {
            return [$cssFiles];
        }

        return $cssFiles;
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
        if(!file_exists($file = $this->path.DIRECTORY_SEPARATOR.'themes.json')){
            return [];
        }

        return json_decode(file_get_contents($file), true);
    }
}