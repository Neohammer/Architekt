<?php

namespace Architekt\Installer;

class Project
{

    private Architekt $architekt;
    public string $code;

    /** @var Application[] */
    public array $applications;

    use DirectoryTrait;

    private array $directories;
    protected bool $fileReplace;
    protected bool $directoryReplace;

    private function __construct(Architekt $architekt, string $code)
    {
        $this->architekt = $architekt;
        $this->code = $code;
        $this->applications = [];
        $this->directories = [];

        $this->directoryReplace = false;
        $this->fileReplace = false;

        $this->build();
    }

    public static function init(Architekt $architekt, string $code): static
    {
        return new self($architekt, $code);
    }

    public function install(): void
    {
        $this->directoriesCreate();
        $this->fileReplace = true;
        $this->filesCreate();

        foreach ($this->applications as $application) {
            $application->install();
        }
    }

    public function build(): void
    {
        $this->directories = [
            'project' => $this->directory(),
            'classes' => $this->directoryClasses(),
            'environments' => $this->directoryEnvironment(),
            'classesControllers' => $this->directoryClassesControllers(),
            'classesUsers' => $this->directoryClassesUsers(),
        ];

        foreach ($this->architekt->json->applications($this->code) as $application) {
            $this->applications[$application] = Application::init($this->architekt, $this, $application);
        }
    }

    public function directoryRead(string $directory, string $directoryAdd = ''): void
    {
        $opendir = opendir($directory);

        while ($file = readdir($opendir)) {
            if (in_array($file, ['.', '..'])) continue;

            $filePath = $directory . DIRECTORY_SEPARATOR . $file;
            if (is_dir($filePath)) {
                $this->directoryCreate(
                    $this->directory() . $directoryAdd . DIRECTORY_SEPARATOR . $file
                );
                $this->directoryRead($directory . DIRECTORY_SEPARATOR . $file, $directoryAdd . DIRECTORY_SEPARATOR . $file);

                continue;
            }

            $this->fileCreate(
                $this->directory() . $directoryAdd . DIRECTORY_SEPARATOR . substr($file, 0, -4),
                $this->template(),
                $filePath
            );
        }
    }

    public function templateVars(): array
    {
        return [
            'PATH_CLASSES' => $this->nameClasses(),
            'PROJECT_CODE' => $this->code,
            'PROJECT_CAMEL' => $this->architekt->toCamelCase($this->code),
            'PROJECT_NAME' => $this->architekt->json->project($this->code)['name'] ?? 'NoName',
        ];
    }

    private function template(): Template
    {
        return (new Template())
            ->setCompileDir($this->architekt->directoryTemporary())
            ->setTemplateDir($this->architekt->directoryTemplatesProject())
            ->assign($this->architekt->templateVars())
            ->assign($this->templateVars());
    }

    private function filesCreate(): void
    {
        $template = $this->template();

        $this
            ->fileCreate(
                $this->directory() . DIRECTORY_SEPARATOR . 'bootstrap.php',
                $template->assign('APPLICATIONS_DOMAINS_BY_ENVIRONMENT', $this->domainsByEnvironment()),
                'bootstrap.php.tpl'
            )
            ->fileCreate(
                $this->directory() . DIRECTORY_SEPARATOR . 'constants.php',
                $template,
                'constants.php.tpl'
            )
            ->fileCreate(
                $this->directoryClasses() . DIRECTORY_SEPARATOR . '_autoloader.php',
                $template,
                '_autoloader.php.tpl'
            )
            ->fileCreate(
                $this->directoryClassesUsers() . DIRECTORY_SEPARATOR . 'User.php',
                $template,
                'User.php.tpl'
            );

        foreach ($this->environments() as $environment) {
            $this->fileCreate(
                $this->directoryEnvironment() . DIRECTORY_SEPARATOR . sprintf('config.%s.php', $environment),
                $template->assign('ENVIRONMENT', $environment)->assign('APPLICATIONS_URLS_PRIMARY', $this->primaryUrls($environment)),
                'config.environment.php.tpl'
            );
        }

    }

    public function environments(): array
    {
        $environments = [];

        foreach ($this->applications as $application) {
            $environments += $application->environments();
        }

        return array_unique($environments);
    }

    public function primaryUrls(string $environment): array
    {
        $urls = [];

        foreach ($this->applications as $application) {
            $urlApplicationPrimary = $application->primaryUrl($environment);
            if ($urlApplicationPrimary) {
                $urls[strtoupper($application->code)] = $urlApplicationPrimary;
            }
        }

        return $urls;
    }

    public function domainsByEnvironment(): array
    {
        $domains = [];

        foreach ($this->applications as $application) {
            foreach ($application->environments() as $environment) {
                $domainsApplication = $application->domains($environment);
                foreach ($domainsApplication as $domainApplication) {
                    if(!array_key_exists($environment,$domains)){
                        $domains[$environment] = [];
                    }
                    $domains[$environment][] = $domainApplication;
                }
            }
        }

        return $domains;
    }

    /**
     * @return Application[]
     */
    public function applicationsWithCdn(string $cdnCode): array
    {
        $applications = [];
        foreach ($this->applications as $application) {
            if ($application->cdnUsed === $cdnCode) {
                $applications[] = $application;
            }
        }

        return $applications;
    }

    public function directory(): string
    {
        return $this->architekt->directoryInstall() . DIRECTORY_SEPARATOR . $this->code;
    }

    private function directoryClasses(): string
    {
        return $this->directory() . DIRECTORY_SEPARATOR . $this->nameClasses();
    }

    private function nameClasses(): string
    {
        return  'classes';
    }

    public function directoryClassesUsers(): string
    {
        return $this->directoryClasses() . DIRECTORY_SEPARATOR . 'Users';
    }

    public function directoryClassesControllers(): string
    {
        return $this->directoryClasses() . DIRECTORY_SEPARATOR . 'Controllers';
    }

    public function directoryEnvironment(): string
    {
        return $this->directory() . DIRECTORY_SEPARATOR . 'environments';
    }
}