<?php

namespace Architekt\Installer;

use Architekt\Installer\Json\ArchitektJson;
use Architekt\Installer\Json\WebVendorsJson;
use Architekt\Installer\Json\ThemesJson;

class Architekt
{
    private string $installPath;

    /** @var Project[] $projects */
    private array $projects;


    use DirectoryTrait;

    private array $directories;
    protected bool $fileReplace;
    protected bool $directoryReplace;

    public ArchitektJson $json;
    public WebVendorsJson $webVendorsJson;
    public ThemesJson $themesJson;

    private function __construct(string $installPath)
    {
        $this->installPath = $installPath;
        $this->projects = [];
        $this->directories = [];
        $this->fileReplace = false;
        $this->directoryReplace = false;
        $this->json = ArchitektJson::init($this->installPath);
        $this->webVendorsJson = WebVendorsJson::init($this->directoryFilesWebVendors());
        $this->themesJson = ThemesJson::init($this->directoryFilesThemes());

        $this->build();
    }

    public static function init(string $installPath): static
    {
        return new self($installPath);
    }

    public function install(): void
    {
        $this->directoriesCreate();
        $this->fileReplace = true;
        $this->filesCreate();

        foreach ($this->projects as $project) {
            $project->install();
        }
    }

    private function build(): void
    {
        $this->directories = [
            'cache' => $this->directoryCache(),
            'filer' => $this->directoryFiler(),
            'tmp' => $this->directoryTemporary()
        ];

        foreach ($this->json->projects() as $project) {
            $this->projects[$project] = Project::init($this, $project);
        }

    }

    private function filesCreate(): void
    {
        /* ->updateHomePlugin(
         $project,
         $application
     )
         ->updateRedirectPlugin(
             $project,
             $application
         );
 */
    }

    public function templateVars(): array
    {
        return [
            'PATH_CACHE' => $this->nameCache(),
            'PATH_FILER' => $this->nameFiler(),
        ];
    }

    public function nameFiler(): string
    {
        return ($this->json->filer() ?? 'Filer');
    }

    public function nameCache(): string
    {
        return ($this->json->cache() ?? 'Cache');
    }

    public function directoryInstall(): string
    {
        return $this->installPath;
    }

    public function directoryCache(): string
    {
        return $this->directoryInstall() . DIRECTORY_SEPARATOR . $this->nameCache() ;
    }

    public function directoryFiler(): string
    {
        return $this->directoryInstall() . DIRECTORY_SEPARATOR . $this->nameFiler();
    }


    private function directoryFiles(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'Files';
    }

    public function directoryFilesWebVendors(): string
    {
        return $this->directoryFiles() . DIRECTORY_SEPARATOR . 'WebVendors';
    }

    public function directoryFilesThemes(): string
    {
        return $this->directoryFiles() . DIRECTORY_SEPARATOR . 'Themes';
    }

    public function directoryTemplatesApplication(): string
    {
        return $this->directoryFiles() . DIRECTORY_SEPARATOR . 'Application';
    }

    public function directoryTemplatesProject(): string
    {
        return $this->directoryFiles() . DIRECTORY_SEPARATOR . 'Project';
    }

    public function directoryPlugins(): string
    {
        return $this->directoryFiles() . DIRECTORY_SEPARATOR . 'Plugins';
    }

    public function directoryTemporary(): string
    {
        return $this->directoryCache() . DIRECTORY_SEPARATOR . 'architektTmp';
    }

    public function toCamelCase(string $string): string
    {
        return ucfirst($string);
    }
}