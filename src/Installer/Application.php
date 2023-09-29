<?php

namespace Architekt\Installer;

use Architekt\DB\Database;
use Users\User;

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

    public function sql(): static
    {


        return $this;
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

            if (!$this->cdnUsed) {
                $this->webVendors = $this->webVendors();
                if ($theme = $this->theme()) {
                    $this->themes[] = $theme;
                }
            }
        } else {
            $applications = $this->project->applicationsWithCdn($this->code);
            if ($applications) {
                foreach ($applications as $application) {
                    $this->webVendors += ($application->webVendors() ?? []);
                    if ($theme = $application->theme()) {
                        $this->themes[] = $theme;
                    }
                }
            }
        }

        if ($this->webVendors) {
            $this->webVendors = array_unique($this->webVendors);
            $this->directories[] = $this->directoryWebVendors();
            foreach ($this->webVendors as $webVendor) {
                $this->directories[] = $this->directoryWebVendors() . DIRECTORY_SEPARATOR . $webVendor;
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

    private function filesCreate(): void
    {
        $template = $this->template();

        $applications = [];
        if ($this->isCdn) {
            foreach ($this->project->environments() as $environment) {
                if ($cors = $this->generateCdnCors($environment)) {
                    $this->fileCreate(
                        sprintf(
                            $this->directoryWeb() . DIRECTORY_SEPARATOR . '.htaccess%s',
                            ($environment === 'local' ? '' : '.' . $environment)
                        ),
                        $template->assign('CORS_VALUES', $cors),
                        '.htaccess-cdn.tpl'
                    );
                }
            }

            $applications = $this->project->applicationsWithCdn($this->code);
        } elseif(!$this->cdnUsed) {
            $applications = [$this];
        }

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

        if ($this->themes) {
            foreach ($this->themes as $theme) {
                $directoryThemeFrom = $this->architekt->directoryFilesThemes() . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR;
                $directoryThemeTo = $this->directoryWebThemes() . DIRECTORY_SEPARATOR . $theme. DIRECTORY_SEPARATOR;

                foreach ($this->architekt->themesJson->javascripts($theme) as $js) {
                    $this->fileCopy(
                        $directoryThemeFrom. $js . '.js',
                        $directoryThemeTo . $js . '.js'
                    );
                }
                foreach ($this->architekt->themesJson->styleSheets($theme) as $css) {
                    $this->fileCopy(
                        $directoryThemeFrom . $css . '.css',
                        $directoryThemeTo  . $css . '.css'
                    );
                }

                if ($directoryImage = $this->architekt->themesJson->directoryImages($theme)) {
                    $this->directoryCopy(
                        $directoryThemeFrom . $directoryImage,
                        $directoryThemeTo . $directoryImage
                    );
                }
            }
        }

        if ($this->isCdn) {
            return;
        }

        $this
            ->fileCreate(
                $this->project->directoryClassesControllers() . DIRECTORY_SEPARATOR . sprintf('%sController.php', $this->architekt->toCamelCase($this->code)),
                $template,
                'ParentApplicationController.php.tpl'
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

            if (substr($file, -4, 4) === '.tpl') {
                $this->fileCreate(
                    $this->directory() . $directoryAdd . DIRECTORY_SEPARATOR . substr($file, 0, -4),
                    $this->template(),
                    $filePath
                );
            } else {
                $this->fileCopy(
                    $filePath,
                    $this->directory() . $directoryAdd . DIRECTORY_SEPARATOR . $file
                );
            }
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
                'APPLICATION_NAME' => $name = ($this->architekt->json->applicationName($this->project->code, $this->code) ?? $this->code),
                'APPLICATION_NAME_CAMEL' => $this->architekt->toCamelCase($name),
                'APPLICATION_CAMEL' => $this->architekt->toCamelCase($this->code),
                'APPLICATION_UPPER' => strtoupper($this->code),
                'APPLICATION_CDN' => (bool)$this->cdnUsed,
                'APPLICATION_MEDIAS' => $this->cdnUsed ? false : '/medias/',
                'APPLICATION_IS_CDN' => $this->isCdn,
                'APPLICATION_CDN_CODE_UPPER' => strtoupper($this->cdnUsed ?? ''),
                'APPLICATION_USER' => false,
                'APPLICATION_THEME' => false,
                'WEBVENDORS_FILES' => $this->webVendorsFiles(),
                'THEME_FILES' => $this->themeFiles(),
            ]);

        if ($theme = $this->theme()) {
            $template->assign([
                'APPLICATION_THEME' => $theme,
                'APPLICATION_THEME_IMAGES' => $this->architekt->themesJson->directoryImages($theme) ?? 'images',
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

        $this->installPlugin('Architekt');
    }

    public function installPlugin(string $name): void
    {
        $baseDirectory = $this->architekt->directoryPlugins() . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;

        if (is_dir($applicationDirectory = $baseDirectory . 'application')) {
            $this->directoryRead($applicationDirectory);
        }

        if (is_dir($projectDirectory = $baseDirectory . 'project')) {
            $this->project->directoryRead($projectDirectory);
        }


        if(file_exists($sql  = $baseDirectory . 'requests.sql')){
            if($databaseCode = $this->database()){
                $database = $this->architekt->database('local',$databaseCode);
            }
            if($database){

                Database::configure(
                    $this->code,
                    $database['engine'],
                    $database['host'],
                    $database['user'],
                    $database['password'],
                    //$database['name']
                );

                Database::get($this->code)->databaseCreate($database['name']);

                $user = new User();

                if( Database::engine($this->code)->motor()->databaseCreate($user, $database['name']) )
                {
                    Database::configure(
                        $this->code,
                        $database['engine'],
                        $database['host'],
                        $database['user'],
                        $database['password'],
                        $database['name']
                    );
                }

                var_dump($query);

            }
        }

    }

    public function webVendors(): ?array
    {
        $webVendors = $this->architekt->json->applicationWebVendors($this->project->code, $this->code) ?? [];

        //architekt is mandatory web vendor
        if (!in_array('architekt', $webVendors)) {
            $webVendors[] = 'architekt';
        }

        return $this->architekt->webVendorsJson->addPrerequisites($webVendors);
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
            'directory' => $this->nameThemes() . '/',
            'javascripts' => [],
            'stylesheets' => []
        ];
        if ($theme = $this->theme()) {
            foreach ($this->architekt->themesJson->javascripts($theme) as $js) {
                $files['javascripts'][] = $theme . '/' . $js;
            }
            foreach ($this->architekt->themesJson->styleSheets($theme) as $css) {
                $files['stylesheets'][] = $theme . '/' . $css;
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

    private function database(): ?string
    {
        return $this->architekt->json->application($this->project->code,$this->code)['database'] ?? null;
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
        return $this->directory() . DIRECTORY_SEPARATOR . 'web';
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
        foreach ($this->project->applicationsWithCdn($this->code) as $application) {
            foreach ($application->urls($environment) as $url) {
                $cors[] = preg_quote($url);
            }
        }

        return $cors;
    }
}