<?php

namespace Architekt\Installer\Json;

use stdClass;

class ArchitektJson
{
    private string $path;
    private array $content;

    private function __construct(
        string $path
    ){
        $this->path = $path;
        $this->content = $this->read();
    }


    public static function init(string $path): static
    {
        if(!file_exists($file = $path.DIRECTORY_SEPARATOR.'architekt.json')){
            file_put_contents($file , '{}');
        }

        return new self($path);
    }

    public function filer(): ?string
    {
        return $this->content['filer'] ?? null;
    }

    public function cache(): ?string
    {
        return $this->content['cache'] ?? null;
    }

    public function databases(): ?array
    {
        return $this->content['databases'] ?? null;
    }

    public function project(string $project): ?array
    {
        return $this->content['projects'][$project] ?? null;
    }

    public function projects(): ?array
    {
        return $this->content['projects'] ? array_keys($this->content['projects']) : null;
    }

    public function applications(string $project): ?array
    {
        if(!$this->project($project)){
            return [];
        }

        return array_keys($this->project($project)['applications']);
    }

    public function application(string $project, string $application): ?array
    {
        return $this->project($project)['applications'][$application] ?? null;
    }

    public function isCdnApplication(string $project, string $application): bool
    {
        return ($this->application($project, $application)['cdn'] ?? false) === true;
    }

    public function cdnApplication(string $project, string $application): ?string
    {
        return is_string($this->application($project, $application)['cdn'] ?? false) ? $this->application($project, $application)['cdn'] : null;
    }

    public function applicationEnvironments(string $project, string $application): ?array
    {
        return $this->application($project, $application)['environments'] ? array_keys($this->application($project, $application)['environments']) : null;
    }

    public function applicationWebVendors(string $project, string $application): ?array
    {
        return $this->application($project, $application)['vendors'] ?? null;
    }

    public function applicationUrls(string $project, string $application, string $environment): array
    {
        $urls = $this->application($project, $application)['environments'][$environment] ?? null;
        if(!$urls){
            return [];
        }
        if(!is_array($urls)){
            return [$urls];
        }

        return $urls;
    }

    public function applicationTheme(string $project, string $application): ?string
    {
        return $this->application($project, $application)['theme'] ?? null;
    }

    public function applicationUser(string $project, string $application): ?string
    {
        return $this->application($project, $application)['user'] ?? null;
    }

    public function applicationName(string $project, string $application): ?string
    {
        return $this->application($project, $application)['name'] ?? null;
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
        if(!file_exists($file = $this->path.DIRECTORY_SEPARATOR.'architekt.json')){
            return [];
        }

        return json_decode(file_get_contents($file), true);
    }
}