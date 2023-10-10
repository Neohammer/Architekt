<?php

namespace Architekt\Installer;

use Architekt\Installer\Json\ArchitektJson;
use Architekt\Installer\Json\ThemesJson;
use Architekt\Installer\Json\WebVendorsJson;


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
        /*

                if(!function_exists('_architecktAutoloader')){
                    require($this->directoryPlugins().DIRECTORY_SEPARATOR.'Architekt'.DIRECTORY_SEPARATOR.'project'.DIRECTORY_SEPARATOR)
        }
                */
        $this->build();
    }

    public static function init(string $installPath): static
    {
        return new self($installPath);
    }

    public function toJson(): static
    {
        Project::datatablesRequiredToJson(
            $this->directoryTemplatesProject()
        );

        return $this;
    }

    public function install(): void
    {
        $environment = 'local';
        Command::info(sprintf('%s - install %s', 'architekt', $environment));

        $this->directoriesCreate();
        $this->fileReplace = true;
        $this->filesCreate();

        foreach ($this->projects as $project) {
            $project->install($environment);
        }
    }

    private function build(): void
    {
        //Command::info(sprintf('%s - build', 'architekt'));

        $this->directories = [
            'cache' => $this->directoryCache(),
            'filer' => $this->directoryFiler(),
            'tmp' => $this->directoryTemporary()
        ];

        foreach ($this->json->projects() as $project) {
            $this->projects[$project] = Project::init($this, $project);
        }

    }

    public function databases(): ?array
    {
        return $this->json->databases();
    }

    public function database(string $environment, string $name): ?array
    {
        $databases = $this->databases();

        if (!array_key_exists($name, $databases)) {
            return null;
        }

        if (!array_key_exists($environment, $databases[$name])) {
            return null;
        }
        $databases[$name][$environment]['prefix'] ??= 'at_';

        return $databases[$name][$environment];
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

    public function installController(string $controllerCode, string $projectCode, string $applicationCode, string $pluginName)
    {
        /** @var Application $application */
        $application = ($project = $this->projects[$projectCode])->applications[$applicationCode] ?? null;

        if (!$application) {
            Command::error(sprintf('Application introuvable %s > %s', $projectCode, $applicationCode));
            exit();
        }

        $project->databaseConnect();

        $plugin = Plugin::init($this, $project, $application, $pluginName);
        $plugin->initEntity();
        $plugin->fileReplace = true;
        $plugin->installController($controllerCode);

        $plugin
            ->fileCreate(
                $application->directoryControllers() . DIRECTORY_SEPARATOR . $controllerCode . 'Controller.php',
                $plugin->template()
                    ->assign('CONTROLLER_CREATE_NAME', $controllerCode)
                    ->assign('CONTROLLER_CREATE_NAMESPACE', '')
                    ->assign('CONTROLLER_CREATE_CLASS', $controllerCode),
                './../templates/controllerBase.tpl'
            )
            ->directoryCreate(
                $application->directoryViews() . DIRECTORY_SEPARATOR . $controllerCode
            );
    }

    public function installSubController(string $controllerCode, string $controllerSubCode, string $projectCode, string $applicationCode, string $pluginName)
    {
        /** @var Application $application */
        $application = ($project = $this->projects[$projectCode])->applications[$applicationCode] ?? null;

        if (!$application) {
            Command::error(sprintf('Application introuvable %s > %s', $projectCode, $applicationCode));
            exit();
        }

        $project->databaseConnect();

        $plugin = Plugin::init($this, $project, $application, $pluginName);
        $plugin->initEntity();
        $plugin->fileReplace = true;
        $plugin->installController($controllerCode . '/' . $controllerSubCode);

        $plugin
            ->directoryCreate(
                $controllerDir = $application->directoryControllers() . DIRECTORY_SEPARATOR . $controllerCode
            )
            ->fileCreate(
                $controllerDir . DIRECTORY_SEPARATOR . $controllerSubCode . 'Controller.php',
                $plugin->template()
                    ->assign('CONTROLLER_CREATE_NAME', $controllerCode . '/' . $controllerSubCode)
                    ->assign('CONTROLLER_CREATE_NAMESPACE', '\\' . $controllerCode)
                    ->assign('CONTROLLER_CREATE_CLASS', $controllerSubCode)
                ,
                './../templates/controllerBase.tpl'
            )
            ->directoryCreate(
                $application->directoryViews() . DIRECTORY_SEPARATOR . $controllerCode
            )
            ->directoryCreate(
                $application->directoryViews() . DIRECTORY_SEPARATOR . $controllerCode . DIRECTORY_SEPARATOR . $controllerSubCode
            );
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
        return $this->directoryInstall() . DIRECTORY_SEPARATOR . $this->nameCache();
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

    public function updateWebVendors(string $projectCode, string $applicationCode, string $environment = 'local')
    {
        if (!($project = $this->projects[$projectCode] ?? null)) {
            Command::error(sprintf('Unknown project %s', $projectCode));
            exit();
        }
        if (!($application = $project->applications[$applicationCode] ?? null)) {
            Command::error(sprintf('Unknown application %s on project %s', $applicationCode, $projectCode));
            exit();
        }

        $project->databaseConnect($environment);

        $application->updateWebVendors($environment);

    }
}