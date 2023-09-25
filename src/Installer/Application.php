<?php

namespace Architekt\Installer;

class Application
{
    private Architekt $architekt;

    private Project $project;

    public string $code;

    public bool $isCdn;

    public ?string $cdnUsed;

    use DirectoryTrait;

    private array $directories;
    private array $webVendors;
    private array $themes;
    protected bool $fileReplace;
    protected bool $directoryReplace;

    public function __construct(Architekt $architekt, Project $project, string $code)
    {
        $this->architekt = $architekt;
        $this->project = $project;
        $this->code = $code;
        $this->fileReplace = false;
        $this->directoryReplace = false;
        $this->directories = [];
        $this->isCdn = false;
        $this->webVendors = [];
        $this->themes = [];
        $this->cdnUsed = null;

        $this->build();
    }


    public static function init(Architekt $architekt, Project $project, string $code): static
    {
        return new self($architekt, $project, $code);
    }

    public function install(): void
    {
        $this->directoriesCreate();
        $this->fileReplace = true;
        $this->filesCreate();

        $this->installPlugins();
    }

    private function build(): void
    {
        $this->directories = [
            'application' => $this->directory(),
            'web' => $this->directoryWeb(),
        ];

        $this->isCdn = $this->architekt->json->isCdnApplication($this->project->code, $this->code);
        if (!$this->isCdn) {
            $this->cdnUsed = $this->architekt->json->cdnApplication($this->project->code, $this->code);

            $this->directories += [
                'controllers' => $this->directoryControllers(),
                'views' => $this->directoryViews(),
            ];
        } else {
            $applications = $this->project->applicationsWithCdn($this->code);
            if ($applications) {
                foreach ($applications as $application) {
                    $this->webVendors += ($application->webVendors() ?? []);
                    if ($theme = $application->theme()) {
                        $this->themes[] = $theme;
                    }
                }

                if ($this->webVendors) {
                    $this->webVendors = array_unique($this->webVendors);
                    $this->directories[] = $this->directoryWebVendors();
                    foreach ($this->webVendors as $webVendor) {
                        $this->directories[] = $this->directoryWebVendors() . DIRECTORY_SEPARATOR . $webVendor;
                    }
                }
            }

            if ($this->themes) {
                $this->themes = array_unique($this->themes);
                $this->directories[] = $this->directoryWebThemes();
                foreach ($this->themes as $theme) {
                    $this->directories[] = $this->directoryWebThemes() . DIRECTORY_SEPARATOR . $theme;
                }
            }
        }

    }

    private function filesCreate(): void
    {
        $template = $this->template();

        if ($this->isCdn) {


            foreach ($this->project->environments() as $environment){
                if($cors = $this->generateCdnCors($environment)){
                    $this->fileCreate(
                        sprintf(
                            $this->directoryWeb() . DIRECTORY_SEPARATOR . '.htaccess%s',
                            ($environment === 'local' ? '':'.'.$environment)
                        ),
                        $template->assign('CORS_VALUES', $cors),
                        '.htaccess-cdn.tpl'
                    );
                }
            }

            if ($this->themes) {
                foreach ($this->themes as $theme) {
                    $this->directoryCreate(
                        $this->directoryWebThemes() . DIRECTORY_SEPARATOR . $theme
                    );

                    foreach ($this->architekt->themesJson->javascripts($theme) as $js) {
                        $this->fileCopy(
                            $this->architekt->directoryFilesThemes() . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . $js . '.js',
                            $this->directoryWebThemes() . DIRECTORY_SEPARATOR . $js . '.js'
                        );
                    }
                    foreach ($this->architekt->themesJson->styleSheets($theme) as $css) {
                        $this->fileCopy(
                            $this->architekt->directoryFilesThemes() . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . $css . '.css',
                            $this->directoryWebThemes() . DIRECTORY_SEPARATOR . $css . '.css'
                        );
                    }

                    if ($directoryImage = $this->architekt->themesJson->directoryImages($theme)) {
                        $this->directoryCopy(
                            $this->architekt->directoryFilesThemes() . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . $directoryImage,
                            $this->directoryWebThemes() . DIRECTORY_SEPARATOR . $directoryImage
                        );
                    }
                }
            }


            $applications = $this->project->applicationsWithCdn($this->code);
            if ($applications) {
                $files = ['javascripts' => [], 'stylesheets' => []];
                foreach ($applications as $application) {
                    $applicationFiles = $application->webVendorsFiles();
                    $files['javascripts'] += $applicationFiles['javascripts'];
                    $files['stylesheets'] += $applicationFiles['stylesheets'];
                }
                if ($files['javascripts']) {
                    $files['javascripts'] = array_unique($files['javascripts']);
                    foreach ($files['javascripts'] as $file) {
                        $this->fileCopy(
                            $this->architekt->directoryFilesWebVendors() . DIRECTORY_SEPARATOR . $file . '.js',
                            $this->directoryWebVendors() . DIRECTORY_SEPARATOR . $file . '.js'
                        );
                    }
                }
                if ($files['stylesheets']) {
                    $files['stylesheets'] = array_unique($files['stylesheets']);
                    foreach ($files['stylesheets'] as $file) {
                        $this->fileCopy(
                            $this->architekt->directoryFilesWebVendors() . DIRECTORY_SEPARATOR . $file . '.css',
                            $this->directoryWebVendors() . DIRECTORY_SEPARATOR . $file . '.css'
                        );
                    }
                }
            }

            return;
        }

        $this
            ->fileCreate(
                $this->project->directoryClassesControllers() . DIRECTORY_SEPARATOR . sprintf('%sController.php', $this->architekt->toCamelCase($this->code)),
                $template->assign([
                    'WEBVENDORS_FILES'=> $this->webVendorsFiles(),
                    'THEME_FILES'=> $this->themeFiles(),
                ]),
                'ParentApplicationController.php.tpl'
            )
            ->fileCreate(
                $this->directory() . DIRECTORY_SEPARATOR . 'bootstrap.php',
                $template,
                'bootstrap.php.tpl'
            )
            ->fileCreate(
                $this->directory() . DIRECTORY_SEPARATOR . 'constants.php',
                $template,
                'constants.php.tpl'
            )
            ->directoryCopy(
                $this->architekt->directoryTemplatesApplication() . DIRECTORY_SEPARATOR . 'interface',
                $this->directoryViews() . DIRECTORY_SEPARATOR . 'interface'
            )
            ->fileCreate(
                $this->directoryWeb() . DIRECTORY_SEPARATOR . '.htaccess',
                $template,
                '.htaccess.tpl'
            )
            ->fileCreate(
                $this->directoryWeb() . DIRECTORY_SEPARATOR . 'index.php',
                $template,
                'index.php.tpl'
            );

        $applicationUser = $this->user() ?? 'User';

        if ($applicationUser !== 'User') {
            $applicationUserClass = $this->architekt->toCamelCase($applicationUser);
            $this
                ->fileCreate(
                    $this->project->directoryClassesUsers() . DIRECTORY_SEPARATOR . $applicationUserClass . '.php',
                    $template,
                    'ApplicationUser.php.tpl'
                )
                ->fileCreate(
                    $this->directoryControllers() . DIRECTORY_SEPARATOR . $applicationUserClass . 'Controller.php',
                    $template,
                    'ApplicationUserController.php.tpl'
                );

        }

        $this
            ->fileCopy(
                $this->architekt->directoryTemplatesApplication() . DIRECTORY_SEPARATOR . 'UserInterface.php.tpl',
                $this->project->directoryClassesUsers() . DIRECTORY_SEPARATOR . 'UserInterface.php',
            )
            ->fileCopy(
                $this->architekt->directoryTemplatesApplication() . DIRECTORY_SEPARATOR . 'UserLoginTrait.php.tpl',
                $this->project->directoryClassesUsers() . DIRECTORY_SEPARATOR . 'UserLoginTrait.php',
            );

    }

    private function directoryRead(string $directory, string $directoryAdd = ''): void
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

    private function template(): Template
    {
        $template = (new Template())
            ->setCompileDir($this->architekt->directoryTemporary())
            ->setTemplateDir($this->architekt->directoryTemplatesApplication())
            ->assign($this->project->templateVars())
            ->assign([
                'APPLICATION_CODE' => $this->code,
                'APPLICATION_NAME' => $this->architekt->json->applicationName($this->project->code, $this->code) ?? $this->code,
                'APPLICATION_CAMEL' => $this->architekt->toCamelCase($this->code),
                'APPLICATION_UPPER' => strtoupper($this->code),
                'APPLICATION_CDN_CODE_UPPER' => strtoupper($this->cdnUsed ?? ''),
                'APPLICATION_USER' => false,
            ]);

        if($theme = $this->theme()){
            $template->assign([
                'THEME' => $theme,
                'THEME_IMAGES' => $this->architekt->themesJson->directoryImages($theme) ?? 'images',
            ]);
        }
        $applicationUser = $this->user() ?? 'User';
        if ($applicationUser !== 'User') {
            $applicationUserLower = strtolower($applicationUser);
            $applicationUserClass = $this->architekt->toCamelCase($applicationUser);

            $template->assign([
                'APPLICATION_USER' => true,
                'APPLICATION_USER_CAMEL' => $applicationUserClass,
                'APPLICATION_USER_LOW' => $applicationUserLower
            ]);
        }

        return $template;
    }


    public function installPlugins(): void
    {
        if ($this->isCdn) {
            return;
        }

        $this->directoryRead(
            $this->architekt->directoryPlugins() . DIRECTORY_SEPARATOR . 'Install' . DIRECTORY_SEPARATOR . 'application'
        );
        $this->project->directoryRead(
            $this->architekt->directoryPlugins() . DIRECTORY_SEPARATOR . 'Install' . DIRECTORY_SEPARATOR . 'project'
        );
    }

    public function webVendors(): ?array
    {
        $webVendors = $this->architekt->json->applicationWebVendors($this->project->code, $this->code);

        if ($webVendors) {
            $webVendors = array_merge(['architekt'], $webVendors);
            $webVendors = $this->architekt->webVendorsJson->addPrerequisites($webVendors);
        }

        return $webVendors;
    }

    public function theme(): ?string
    {
        return $this->architekt->json->applicationTheme($this->project->code, $this->code) ?? null;
    }

    public function user(): ?string
    {
        return $this->architekt->json->applicationUser($this->project->code, $this->code) ?? null;
    }

    public function webVendorsFiles(): ?array
    {
        $webVendors = $this->webVendors();
        $javascripts = $styleSheets = [];

        if ($webVendors) {
            foreach ($webVendors as $webVendor) {

                foreach ($this->architekt->webVendorsJson->javascripts($webVendor) as $js) {
                    $javascripts[] = sprintf(
                        '%s/%s',
                        $webVendor,
                        $js,
                    );
                }

                foreach ($this->architekt->webVendorsJson->styleSheets($webVendor) as $css) {
                    $styleSheets[] = sprintf(
                        '%s/%s',
                        $webVendor,
                        $css,
                    );
                }
            }
        }

        return [
            'directory' => 'vendors/',
            'javascripts' => $javascripts,
            'stylesheets' => $styleSheets
        ];
    }

    public function themeFiles(): array
    {
        $files = [
            'directory' => $this->nameThemes().'/',
            'javascripts' =>[],
            'stylesheets' => []
        ];
        if($theme = $this->theme()) {
            foreach ($this->architekt->themesJson->javascripts($theme) as $js) {
                $files['javascripts'][] =   $theme.'/'.$js;
            }
            foreach ($this->architekt->themesJson->styleSheets($theme) as $css) {
                $files['stylesheets'][] = $theme.'/'. $css ;
            }
        }

        return $files;
    }

    public function environments(): array
    {
        return $this->architekt->json->applicationEnvironments($this->project->code, $this->code);
    }

    public function urls(string $environment): array
    {
        return $this->architekt->json->applicationUrls($this->project->code, $this->code, $environment) ?? [];
    }

    public function domains(string $environment): array
    {
        $urls = $this->urls($environment);
        if (!$urls) {
            return [];
        }

        $domains = [];
        foreach ($urls as $url) {
            if (preg_match('|^https?://([^/]+)$|', $url, $found)) {
                $domains[] = $found[1];
            }
        }

        return $domains;
    }

    public function primaryUrl(string $environment): ?string
    {
        $urls = $this->urls($environment);

        if (!$urls) {
            return null;
        }

        return $urls[0];
    }

    private function directory(): string
    {
        return $this->project->directory() . DIRECTORY_SEPARATOR . '_' . $this->code;
    }

    private function directoryControllers(): string
    {
        return $this->directory() . DIRECTORY_SEPARATOR . 'controllers';
    }

    private function directoryViews(): string
    {
        return $this->directory() . DIRECTORY_SEPARATOR . 'views';
    }

    private function directoryWeb(): string
    {
        return $this->directory() . DIRECTORY_SEPARATOR . 'www';
    }

    private function directoryWebVendors(): string
    {
        return $this->directoryWeb() . DIRECTORY_SEPARATOR . 'vendors';
    }

    private function directoryWebThemes(): string
    {
        return $this->directoryWeb() . DIRECTORY_SEPARATOR . $this->nameThemes();
    }

    private function nameThemes(): string
    {
        return 'themes';
    }


    private function generateCdnCors(string $environment): array
    {
        $cors = [];
        foreach($this->project->applicationsWithCdn($this->code) as $application){
            foreach($application->urls($environment) as $url){
                $cors[] = preg_quote($url);
            }
        }

        return $cors;
    }
}