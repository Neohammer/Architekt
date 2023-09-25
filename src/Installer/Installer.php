<?php

namespace Architekt\Installer;

class Installer
{
    private string $path;
    private Project[] $projects;
    private ArchitektJson $json;
    private WebVendorsJson $jsonLibraries;
    private ThemesJson $jsonThemes;

    private bool $replaceFile = true;

    public function __construct(
        string $path
    )
    {
        $this->path = $path;
        $this->json = ArchitektJson::init($path);
        $this->jsonLibraries = WebVendorsJson::init($this->directoryArchitektLibraries());
        $this->jsonThemes = ThemesJson::init($this->directoryArchitektThemes());
    }

    public static function update(
        string $path
    ): void
    {

        $that = new self($path);
        $projects = $that->json->projects();

        foreach ($projects as $project) {
            echo "" . $project . "\n";
            $applications = $that->json->applications($project);
            foreach ($applications as $application) {
                echo ">" . $application . "\n";
                (new static($path))
                    ->createProjectDirectories($project)
                    ->directoriesApplicationCreate($project, $application);

            }
        }
    }

    private function fileProjectJson(string $project): string
    {
        return $this->directoryProject($project) . DIRECTORY_SEPARATOR . 'architekt.json';
    }

    private function directoryInstall(): string
    {
        return $this->path;
    }

    private function directoryProject(string $project): string
    {
        return $this->directoryInstall() . DIRECTORY_SEPARATOR . $project;
    }

    private function directoryProjectClasses(string $project): string
    {
        return $this->directoryProject($project) . DIRECTORY_SEPARATOR . 'classes';
    }

    private function directoryProjectClassesUsers(string $project): string
    {
        return $this->directoryProjectClasses($project) . DIRECTORY_SEPARATOR . 'Users';
    }

    private function directoryProjectClassesControllers(string $project): string
    {
        return $this->directoryProjectClasses($project) . DIRECTORY_SEPARATOR . 'Controllers';
    }

    private function directoryCache(): string
    {
        return $this->directoryInstall() . DIRECTORY_SEPARATOR . $this->json->cache();
    }

    private function directoryFiler(): string
    {
        return $this->directoryInstall() . DIRECTORY_SEPARATOR . $this->json->filer();
    }


    private function directoryCreate(string $dir): static
    {
        if (!is_dir($dir)) {
            echo sprintf("Creating directory : %s\n", $dir);
            mkdir($dir);
        } else {
            echo sprintf("Directory found : %s\n", $dir);
        }

        return $this;
    }

    private function directoryCopy(string $dir, string $dirTo, bool $recursive = false): static
    {
        $openedDir = opendir($dir);
        $this->directoryCreate($dirTo);
        while ($file = readdir($openedDir)) {

            if (in_array($file, ['.', '..'])) continue;

            $inputFile = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($inputFile)) {
                if ($recursive) {
                    $this->directoryCopy($inputFile, $dirTo . DIRECTORY_SEPARATOR . $file);
                }
                continue;
            }

            if (!is_file($inputFile)) continue;

            $outputFile = $dirTo . DIRECTORY_SEPARATOR . $file;
            if ($this->replaceFile || !file_exists($outputFile)) {
                if (file_exists($outputFile)) unlink($outputFile);
                copy($inputFile, $outputFile);
            }
        }

        return $this;
    }

    private function directoryApplication(string $project, string $application): string
    {
        return $this->directoryProject($project) . DIRECTORY_SEPARATOR . '_' . $application;
    }

    private function directoryApplicationControllers(string $project, string $application): string
    {
        return $this->directoryApplication($project, $application) . DIRECTORY_SEPARATOR . 'controllers';
    }

    private function directoryApplicationViews(string $project, string $application): string
    {
        return $this->directoryApplication($project, $application) . DIRECTORY_SEPARATOR . 'views';
    }

    private function directoryApplicationWeb(string $project, string $application): string
    {
        return $this->directoryApplication($project, $application) . DIRECTORY_SEPARATOR . 'www';
    }

    private function directoryApplicationWebVendors(string $project, string $application): string
    {
        return $this->directoryApplicationWeb($project, $application) . DIRECTORY_SEPARATOR . 'vendors';
    }

    private function directoryApplicationWebThemes(string $project, string $application): string
    {
        return $this->directoryApplicationWeb($project, $application) . DIRECTORY_SEPARATOR . 'themes';
    }


    private function directoryArchitektFiles(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'Files';
    }

    private function directoryArchitektApplicationTemplate(): string
    {
        return $this->directoryArchitektFiles() . DIRECTORY_SEPARATOR . 'Application';
    }

    private function directoryArchitektLibraries(): string
    {
        return $this->directoryArchitektFiles() . DIRECTORY_SEPARATOR . 'Libraries';
    }

    private function directoryArchitektThemes(): string
    {
        return $this->directoryArchitektFiles() . DIRECTORY_SEPARATOR . 'Themes';
    }


    private function toCamelCase(string $string): string
    {
        return ucfirst($string);
    }
    public function createProjectDirectories(
        string $project
    ): static
    {
        $templateProject = $this->templateProject($project);

        $this
            ->directoryCreate(
                $installDirFiler = $this->directoryFiler()
            )
            ->directoryCreate(
                $installDirFiler . DIRECTORY_SEPARATOR . 'Logs'
            )
            ->directoryCreate(
                $this->directoryCache()
            )
            ->directoryCreate(
                $installDirProject = $this->directoryProject($project)
            )
            ->createFile(
                $installDirProject . DIRECTORY_SEPARATOR . 'bootstrap.php',
                $templateProject,
                'bootstrap.php.tpl'
            )
            ->createFile(
                $installDirProject . DIRECTORY_SEPARATOR . 'constants.php',
                $templateProject,
                'constants.php.tpl'
            )
            ->directoryCreate(
                $installDirClasses = $this->directoryProjectClasses($project)
            )
            ->createFile(
                $installDirClasses . DIRECTORY_SEPARATOR . '_autoloader.php',
                $templateProject,
                '_autoloader.php.tpl'
            )
            ->directoryCreate(
                $installDirClassesUsers = $this->directoryProjectClassesUsers($project)
            )
            ->createFile(
                $installDirClassesUsers . DIRECTORY_SEPARATOR . 'User.php',
                $templateProject,
                'User.php.tpl'
            )
            ->directoryCreate(
                $installDirEnvironment = $installDirProject . DIRECTORY_SEPARATOR . 'environment'
            );

        $environments = [];
        foreach ($this->json->applications($project) as $application) {
            foreach ($this->json->applicationEnvironments($project, $application) as $environment) {
                if (!in_array($environment, $environments)) {
                    $environments[] = $environment;

                    $this->createFile(
                        $installDirEnvironment . DIRECTORY_SEPARATOR . sprintf('config.%s.php', $environment),
                        $templateProject->assign('ENVIRONMENT', $environment),
                        'config.environment.php.tpl'
                    );
                }
            }
        }


        return $this;
    }

    private function templateProject(string $project): Template
    {
        $environments = [];
        $applicationsUrls = [];

        foreach ($this->json->applications($project) as $application) {
            if ($this->json->isCdnApplication($project, $application)) {
                continue;
            }
            foreach ($this->json->applicationEnvironments($project, $application) as $environment) {

                if (!array_key_exists($environment, $applicationsUrls)) {
                    $applicationsUrls[$environment] = [];
                }
                $urlsEnvironnement = $this->json->applicationUrls($project, $application, $environment);

                $applicationsUrls[$environment][strtoupper($application)] = $urlsEnvironnement[0];

                if ($cdn = $this->json->cdnApplication($project, $application)) {
                    $applicationsUrls[$environment][strtoupper($cdn)] = $this->json->applicationUrls($project, $cdn, $environment)[0] ?? '#ERROR#';
                }
                if (!array_key_exists($environment, $environments)) {
                    $environments[$environment] = [];
                }
                //$environments[$environment]+= $this->json->applicationUrls($project, $application, $environment) ;
                $environments[$environment] = array_merge($environments[$environment],$urlsEnvironnement);
            }
        }

        return (new Template())
            ->setCompileDir($this->directoryCache())
            ->setTemplateDir(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'Project')
            ->assign([
                'PROJECT' => $project,
                'PROJECT_NAME' => $this->json->project($project)['name'] ?? 'NoName',
                'ENVIRONMENTS' => $environments,
                'APPLICATIONS_URLS' => $applicationsUrls,
                'PATH_CLASSES' => str_replace($this->directoryProject($project), '', $this->directoryProjectClasses($project)),
                'PATH_FILER' => str_replace($this->path, '', $this->directoryFiler()),
                'PATH_CACHE' => str_replace($this->path, '', $this->directoryCache()),
            ]);
    }

    private function templateApplication(string $project, string $application): Template
    {
        return (new Template())
            ->setCompileDir($this->directoryCache())
            ->setTemplateDir($this->directoryArchitektApplicationTemplate())
            ->assign([
                'PROJECT' => $project,
                'PROJECT_CLASS' => $this->toCamelCase($project),
                'PROJECT_NAME' => $this->json->project($project)['name'] ?? 'NoName',
                'APPLICATION' => $application,
                'THEME' => $theme = ($this->json->applicationTheme($project, $application) ?? 'none'),
                'THEME_IMAGES' => $this->jsonThemes->imagesDirectory($theme) ?? 'images',
                'APPLICATION_CLASS' => $this->toCamelCase($application),
                'APPLICATION_CLASS_UPPER' => strtoupper($application)
            ]);
    }

    public function directoriesApplicationCreate(string $project, string $application): static
    {
        $isCdn = $this->json->isCdnApplication($project, $application);

        $templateApplication = $this->templateApplication($project, $application);

        $this->directoryCreate(
            $installDirApplication = $this->directoryApplication($project, $application)
        );

        if ($isCdn) {
            return $this
                ->directoryCreate(
                    $installDirWeb = $this->directoryApplicationWeb($project, $application)
                )
                ->createFile(
                    $installDirWeb . DIRECTORY_SEPARATOR . '.htaccess',
                    $templateApplication->assign('CORS_VALUES', $this->generateCdnCors($application, $project)),
                    '.htaccess-cdn.tpl'
                )
                ->directoryCopy(
                    $this->directoryArchitektFiles() . DIRECTORY_SEPARATOR . 'cdn',
                    $installDirWeb
                )
                ->importCdnLibraries($application, $project)
                ->importCdnThemes($application, $project);

        }


        $this
            ->createFile(
                $installDirApplication . DIRECTORY_SEPARATOR . 'bootstrap.php',
                $templateApplication,
                'bootstrap.php.tpl'
            )
            ->createFile(
                $installDirApplication . DIRECTORY_SEPARATOR . 'constants.php',
                $templateApplication,
                'constants.php.tpl'
            )
            ->directoryCreate(
                $this->directoryProjectClasses($project)
            )
            ->directoryCreate(
                $installDirControllerClasses = $this->directoryProjectClassesControllers($project)
            )
            ->createFile(
                $installDirControllerClasses . DIRECTORY_SEPARATOR . sprintf('%sController.php', $this->toCamelCase($application)),
                $templateApplication->assign('LIBRARIES_FILES', $this->applicationLibrariesFiles($project, $application)),
                'ParentApplicationController.php.tpl'
            )
            ->directoryCreate(
                $this->directoryApplicationControllers($project, $application)
            )
            ->directoryCreate(
                $installDirViews = $this->directoryApplicationViews($project, $application)
            )
            ->directoryCopy(
                $this->directoryArchitektApplicationTemplate() . DIRECTORY_SEPARATOR . 'interface',
                $installDirViews . DIRECTORY_SEPARATOR . 'interface'
            )
            ->directoryCreate(
                $installDirWeb = $this->directoryApplicationWeb($project, $application)
            )
            ->createFile(
                $installDirWeb . DIRECTORY_SEPARATOR . '.htaccess',
                $templateApplication,
                '.htaccess.tpl'
            )
            ->createFile(
                $installDirWeb . DIRECTORY_SEPARATOR . 'index.php',
                $templateApplication,
                'index.php.tpl'
            )
            ->updateHomePlugin(
                $project,
                $application
            )
            ->updateRedirectPlugin(
                $project,
                $application
            );

        $applicationUser = $this->json->application($project, $application)['user'] ?? 'User';

        if ($applicationUser !== 'User') {

            $applicationUserLower = strtolower($applicationUser);
            $applicationUserClass = $this->toCamelCase($applicationUser);

            $templateApplication->assign([
                'APPLICATION_USER_CLASS' => $applicationUserClass,
                'APPLICATION_USER_CLASS_LOW' => $applicationUserLower
            ]);

            $this
                ->directoryCreate(
                    $installDirClassesUsers = $this->directoryProjectClassesUsers($project)
                )
                ->createFile(
                    $installDirClassesUsers . DIRECTORY_SEPARATOR . $applicationUserClass . '.php',
                    $templateApplication,
                    'ApplicationUser.php.tpl'
                )
                ->createFile(
                    $this->directoryApplicationControllers($project, $application) . DIRECTORY_SEPARATOR . $applicationUserClass . 'Controller.php',
                    $templateApplication,
                    'ApplicationUserController.php.tpl'
                );
        }


        return $this;

    }

    public function updateHomePlugin(string $project, string $application): static
    {
        $templateApplication = $this->templateApplication($project, $application);

        return $this
            ->createFile(
                $this->directoryApplicationControllers($project, $application) . DIRECTORY_SEPARATOR . 'HomeController.php',
                $templateApplication,
                'HomeController.php.tpl'
            )
            ->directoryCreate(
                $redirectViewsDirectory = $this->directoryApplicationViews($project, $application) . DIRECTORY_SEPARATOR . 'Home'
            )
            ->createFile(
                $redirectViewsDirectory . DIRECTORY_SEPARATOR . 'index.html',
                $templateApplication,
                'home_index.html.tpl'
            );
    }

    public function updateRedirectPlugin(string $project, string $application): static
    {
        $templateApplication = $this->templateApplication($project, $application);

        return $this
            ->createFile(
                $this->directoryApplicationControllers($project, $application) . DIRECTORY_SEPARATOR . 'RedirectController.php',
                $templateApplication,
                'RedirectController.php.tpl'
            )
            ->directoryCreate(
                $redirectViewsDirectory = $this->directoryApplicationViews($project, $application) . DIRECTORY_SEPARATOR . 'Redirect'
            )
            ->createFile(
                $redirectViewsDirectory . DIRECTORY_SEPARATOR . 'error.html',
                $templateApplication,
                'redirect_error.html.tpl'
            );
    }

    private function createFile(string $file, Template $template, string $fileTemplate): static
    {
        if ($this->replaceFile || !file_exists($file)) {
            echo sprintf("Creating file : %s\n", $file);
            file_put_contents(
                $file,
                $template->fetch($fileTemplate)
            );
        } else {
            echo sprintf("File found : %s\n", $file);
        }

        return $this;
    }

    private function copyFile(string $fileFrom, string $fileTo): static
    {
        if ($this->replaceFile || !file_exists($fileTo)) {
            echo sprintf("Creating file : %s\n", $fileTo);
            if (file_exists($fileTo)) {
                unlink($fileTo);
            }
            copy(
                $fileFrom,
                $fileTo
            );
        } else {
            echo sprintf("File found : %s\n", $fileTo);
        }

        return $this;
    }

    private function generateCdnCors(string $applicationCdn, string $project): array
    {
        $cors = [];
        foreach ($this->json->applications($project) as $application) {
            if ($this->json->cdnApplication($project, $application) === $applicationCdn) {
                foreach ($this->json->applicationEnvironments($project, $application) as $applicationEnvironment) {
                    foreach ($this->json->applicationUrls($project, $application, $applicationEnvironment) as $domain) {

                        $cors[] = [
                            'domain' => preg_quote($domain)
                        ];
                    }
                }
            }
        }

        return $cors;
    }

    private function importCdnLibraries(string $applicationCdn, string $project): static
    {
        $libraries = $this->projectLibraries($project, $applicationCdn);
        if ($libraries) {
            $this->directoryCreate($libraryDir = $this->directoryApplicationWebVendors($project, $applicationCdn));
            foreach ($libraries as $library) {
                $this->directoryCopy(
                    $this->directoryArchitektLibraries() . DIRECTORY_SEPARATOR . $library,
                    $libraryDir . DIRECTORY_SEPARATOR . $library,
                    true
                );
            }
        }

        return $this;
    }

    private function importCdnThemes(string $applicationCdn, string $project): static
    {
        $themes = [];

        foreach ($this->json->applications($project) as $application) {
            if ($this->json->cdnApplication($project, $application) === $applicationCdn
                && $theme = $this->json->applicationTheme($project, $application)) {
                $themes[] = $theme;
            }
        }
        if ($themes) {
            $this->directoryCreate(
                $installThemesDir = $this->directoryApplicationWebThemes($project, $applicationCdn)
            );

            foreach ($themes as $theme) {
                $this->directoryCreate(
                    $installThemeDir = $installThemesDir . DIRECTORY_SEPARATOR . $theme
                );

                if ($files = $this->jsonThemes->libraryCss($theme)) {
                    foreach ($files as $file) {
                        $this->copyFile(
                            $this->directoryArchitektThemes() . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . $file . '.css',
                            $installThemeDir . DIRECTORY_SEPARATOR . $file . '.css'
                        );
                    }
                }

                if ($files = $this->jsonThemes->libraryJavascripts($theme)) {
                    foreach ($files as $file) {
                        $this->copyFile(
                            $this->directoryArchitektThemes() . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . $file . '.js',
                            $installThemeDir . DIRECTORY_SEPARATOR . $file . '.js'
                        );
                    }
                }

                if ($directory = $this->jsonThemes->imagesDirectory($theme)) {
                    $this->directoryCopy(
                        $this->directoryArchitektThemes() . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . $directory,
                        $installThemeDir . DIRECTORY_SEPARATOR . $directory
                    );
                }
            }
        }

        return $this;
    }

    private function projectLibraries(string $project, string $applicationCdn): array
    {
        $libraries = [];
        foreach ($this->json->applications($project) as $application) {
            if ($this->json->cdnApplication($project, $application) === $applicationCdn) {
                $libraries += ($this->applicationLibraries($project, $application) ?? []);
            }
        }
        if ($libraries) {
            $libraries = array_unique($libraries);
        }

        return $libraries;
    }

    private function applicationLibraries(string $project, string $application): array
    {
        $libraries = [];
        $applicationLibraries = ($this->json->applicationLibraries($project, $application) ?? []);

        if ($applicationLibraries) {
            foreach ($applicationLibraries as $applicationLibrary) {
                $requiredLibraries = ($this->jsonLibraries->prerequisites($applicationLibrary) ?? []);
                if ($requiredLibraries) {
                    $libraries += $requiredLibraries;
                }
                $libraries[] = $applicationLibrary;
            }
        }

        return array_unique($libraries);
    }

    private function applicationLibrariesFiles(string $project, string $application): array
    {
        $librariesFiles = [
            'js' => [],
            'css' => []
        ];

        $libraries = $this->applicationLibraries($project, $application);

        foreach ($libraries as $requiredLibrary) {
            if ($js = $this->jsonLibraries->libraryJavascripts($requiredLibrary)) {
                foreach ($js as $jsFile) {
                    $librariesFiles['js'][] = 'vendors/' . $requiredLibrary . '/' . $jsFile;
                }
            }
            if ($css = $this->jsonLibraries->libraryCss($requiredLibrary)) {
                foreach ($css as $cssFile) {
                    $librariesFiles['css'][] = 'vendors/' . $requiredLibrary . '/' . $cssFile;
                }
            }
        }

        $theme = $this->json->applicationTheme($project, $application);
        if ($theme) {
            if ($jsFiles = $this->jsonThemes->libraryJavascripts($theme)) {
                foreach ($jsFiles as $jsFile) {
                    $librariesFiles['js'][] = 'themes/' . $theme . '/' . $jsFile;
                }
            }
            if ($cssFiles = $this->jsonThemes->libraryCss($theme)) {
                foreach ($cssFiles as $cssFile) {
                    $librariesFiles['css'][] = 'themes/' . $theme . '/' . $cssFile;
                }
            }
        }

        return $librariesFiles;
    }
}