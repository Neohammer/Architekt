<?php

namespace Architekt\Installer;

use Architekt\DB\DBConnexion;
use Architekt\DB\DBDatatable;
use Architekt\DB\DBDatatableColumn;
use Architekt\Utility\Settings;

class Application
{

    use DirectoryTrait;

    public bool $isCdn;
    public bool $isAdmin;

    public ?string $cdnUsed;
    public ?string $administration;

    private array $directories;

    private array $webVendors;

    private array $themes;

    protected bool $fileReplace;

    protected bool $directoryReplace;

    protected ?\Architekt\Application $applicationEntity;

    protected ?\Architekt\Plugin $pluginRow;

    /** @var ?\Architekt\Plugin[] $plugins */
    protected ?array $plugins;

    /** @var ?\Architekt\Controller[] $controllers */
    protected ?array $controllers;

    /**
     * @return DBDatatable[]
     */
    public static function datatablesRequired(): array
    {
        $datatables = [];

        $datatables[] = (new DBDatatable('application'))
            ->addColumn(DBDatatableColumn::buildAutoincrement())
            ->addColumn(DBDatatableColumn::buildInt('project_id', 5))
            ->addColumn(DBDatatableColumn::buildString('name', 100))
            ->addColumn(DBDatatableColumn::buildString('name_system', 50))
            ->addColumn(DBDatatableColumn::buildString('settings', 1000)->setDefault(null));

        return $datatables;

    }

    public function __construct(
        private Architekt $architekt,
        private Project   $project,
        public string     $code
    )
    {
        $this->applicationEntity = null;


        $this->fileReplace = false;
        $this->directoryReplace = false;


        $this->directories = [];
        $this->isCdn = false;
        $this->isAdmin = false;
        $this->administration = null;
        $this->cdnUsed = null;
        $this->webVendors = [];
        $this->themes = [];
        $this->pluginRow = null;
        $this->controllers = null;
        $this->plugins = [];

        $this->build();
    }

    public static function init(Architekt $architekt, Project $project, string $code): static
    {
        return new self($architekt, $project, $code);
    }

    public function install(string $environment): void
    {
        $this->datatablesCreate($environment);

        $this->initEntity();

        Command::info(sprintf('%s:%s - Install %s', $this->project->code, $this->code, $environment));
        $this->directoriesCreate();
        $this->fileReplace = true;
        $this->filesCreate();

        Command::info(sprintf('%s:%s - Install plugins', $this->project->code, $this->code));

        /** @var Plugin $plugin */
        foreach ($this->plugins as $plugin) {
            $plugin->install();
        }
    }

    private function datatablesCreate(string $environment): void
    {
        $connexion = DBConnexion::get();
        $databaseInfos = $this->project->databaseInfos($environment);

        if (!$databaseInfos) {
            return;
        }

        foreach (self::datatablesRequired() as $datatable) {
            $prefix = $databaseInfos['prefix'] ?? '';
            if ($prefix) {
                $datatable->prefix($prefix);
            }

            if ($connexion->datatableExists($datatable)) {
                Command::warning(sprintf('%s - Datatable %s already exists', $this->displayName(), $datatable->name()));
                continue;
            }
            $connexion->datatableCreate($datatable);
            Command::info(sprintf('%s - Datatable %s created', $this->displayName(), $datatable->name()));

        }
    }

    public function entity(): \Architekt\Application
    {
        if (!$this->applicationEntity) {
            $this->initEntity();
        }

        return $this->applicationEntity;
    }

    public function initEntity(): void
    {
        $this->applicationEntity = \Architekt\Application::byNameSystem($this->code);
        if ($this->applicationEntity) {
            Command::warning(sprintf('%s:%s application %s already exists in datatable', $this->project->code, $this->code, $this->code));
        } else {
            $this->applicationEntity = (new \Architekt\Application())
                ->_set([
                    $this->project->projectRow,
                    'name' => $this->architekt->json->applicationName($this->project->code, $this->code) ?? $this->code,
                    'name_system' => $this->code
                ]);

            $this->applicationEntity->_save();
            Command::info(sprintf('%s:%s application n°%d created', $this->project->code, $this->code, $this->applicationEntity->_primary()));
        }
    }

    public function buildSettings(): void
    {
        $settings = Settings::byApplication($this->entity());
        $settings->setValue('general', 'type', 'website');

        if ($this->isAdmin) {
            $settings->setValue('general', 'type', 'administration');

            $applications = $this->project->applicationsWithAdministration($this->code);
            foreach ($applications as $application) {
                $settings->addValue('administration', 'applications', $application->entity()->_primary());;
            }
        } elseif ($this->administration) {
            $settings->setValue('general', 'administration', \Architekt\Application::byNameSystem($this->administration)->_primary());
        } else {
            $settings->setValue('general', 'administration', false);
        }

        if ($this->isCdn) {
            $settings->setValue('general', 'type', 'cdn');
            $applications = $this->project->applicationsWithCdn($this->code);
            foreach ($applications as $application) {
                $settings->addValue('cdn', 'applications', $application->entity()->_primary());;
            }
        } elseif ($this->cdnUsed) {
            $settings->setValue('general', 'cdn', \Architekt\Application::byNameSystem($this->cdnUsed)->_primary());
        } else {
            $settings->setValue('general', 'cdn', false);
        }

        foreach ($this->environments() as $environment) {
            $settings->setValue('urls', $environment, $this->primaryUrl($environment));
        }

        if ($this->hasCustomUser()) {
            $settings->setValue('general', 'appUser', $this->user());
        }

        if ($this->isAdmin) {
            $settings->setValue('account', 'create', true);
            $settings->setValue('account', 'create_confirm', false);
            $settings->setValue('account', 'create_login', true);
        } elseif (!$this->isCdn) {
            $settings->setValue('account', 'create', false);
            $settings->setValue('account', 'create_confirm', true);
            $settings->setValue('account', 'create_login', false);
        }

        $this->entity()->_save();

        /** @var Plugin $plugin */
        foreach ($this->plugins as $plugin) {
            $plugin->buildSettings();
        }
    }

    public function hasCustomUser(): bool
    {
        return $this->user() ?? 'User' !== 'User';
    }


    private function build(): void
    {
        //Command::info(sprintf('%s build', $this->displayName()));


        $this->isAdmin = $this->type() === 'administration';
        $this->administration = $this->isAdmin ? null : $this->administration();

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

            $this->plugins['Architekt'] = \Architekt\Installer\Plugin::init($this->architekt, $this->project, $this, 'Architekt');
            //$this->plugins['ArchitektNotifier'] = \Architekt\Installer\Plugin::init($this->architekt, $this->project, $this, 'ArchitektNotifier');
            if ($this->hasCustomUser()) {
                $this->plugins['ArchitektApplicationUser'] = \Architekt\Installer\Plugin::init($this->architekt, $this->project, $this, 'ArchitektApplicationUser');
            }
            //$this->plugins['ArchitektProfiles'] = \Architekt\Installer\Plugin::init($this->architekt, $this->project, $this, 'ArchitektProfiles');

            if (!$this->cdnUsed) {
                $this->webVendors = $this->webVendorsArray();
                if ($theme = $this->theme()) {
                    $this->themes[] = $theme;
                }
            }
        } else {
            $this->plugins['ArchitektCdn'] = \Architekt\Installer\Plugin::init($this->architekt, $this->project, $this, 'ArchitektCdn');

            $applications = $this->project->applicationsWithCdn($this->code);
            if ($applications) {
                foreach ($applications as $application) {
                    $this->webVendors += ($application->webVendorsArray() ?? []);
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

    public function displayName(): string
    {
        return sprintf(
            '%s:%s -',
            $this->project->code,
            $this->code
        );
    }

    private function filesCreate(): void
    {
        $template = $this->template();

        $applications = [];
        if ($this->isCdn) {
            $applications = $this->project->applicationsWithCdn($this->code);
        } elseif (!$this->cdnUsed) {
            $applications = [$this];
        }

        $this->webVendorsFilesCreate($applications);

        if ($this->themes) {
            foreach ($this->themes as $theme) {
                $directoryThemeFrom = $this->architekt->directoryFilesThemes() . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR;
                $directoryThemeTo = $this->directoryWebThemes() . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR;

                foreach ($this->architekt->themesJson->javascripts($theme) as $js) {
                    $this->fileCopy(
                        $directoryThemeFrom . $js . '.js',
                        $directoryThemeTo . $js . '.js'
                    );
                }
                foreach ($this->architekt->themesJson->styleSheets($theme) as $css) {
                    $this->fileCopy(
                        $directoryThemeFrom . $css . '.css',
                        $directoryThemeTo . $css . '.css'
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

    }

    public function templateVars(): array
    {
        $vars = [
            'APPLICATION' => $this->entity(),
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
        ];

        if ($theme = $this->theme()) {
            $vars = array_merge($vars, [
                'APPLICATION_THEME' => $theme,
                'APPLICATION_THEME_IMAGES' => $this->architekt->themesJson->directoryImages($theme) ?? 'images',
            ]);
        }

        $applicationUser = $this->user() ?? 'User';
        if ($applicationUser !== 'User') {
            $applicationUserLower = strtolower($applicationUser);
            $applicationUserClass = $this->architekt->toCamelCase($applicationUser);

            $vars = array_merge($vars, [
                'APPLICATION_USER' => true,
                'APPLICATION_USER_CAMEL' => $applicationUserClass,
                'APPLICATION_USER_LOW' => $applicationUserLower
            ]);
        }

        return $vars;
    }

    public function templateVarsFromApplicationUser(): array
    {
        if (!$this->hasCustomUser()) {
            return [];
        }

        return [
            'APPLICATION_USER' => true,
            'APPLICATION_USER_CAMEL' => $this->architekt->toCamelCase($this->user()),
            'APPLICATION_USER_LOW' => strtolower($this->user())
        ];
    }

    private function template(): Template
    {
        return (new Template())
            ->setCompileDir($this->architekt->directoryTemporary())
            ->setTemplateDir($this->architekt->directoryTemplatesApplication())
            ->assign($this->project->templateVars())
            ->assign($this->templateVars());
    }

    public function webVendorsArray(): ?array
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

    public function type(): ?string
    {
        return $this->architekt->json->applicationType($this->project->code, $this->code) ?? null;
    }

    public function administration(): ?string
    {
        return $this->architekt->json->applicationAdministration($this->project->code, $this->code) ?? null;
    }

    public function user(): ?string
    {
        return $this->architekt->json->applicationUser($this->project->code, $this->code) ?? null;
    }

    public function webVendorsFiles(): ?array
    {
        $webVendors = $this->webVendorsArray();
        $javascripts = $styleSheets = $others = [];

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

                foreach ($this->architekt->webVendorsJson->otherFiles($webVendor) as $file) {
                    $others[] = sprintf(
                        '%s/%s',
                        $webVendor,
                        $file,
                    );
                }
            }
        }

        return [
            'directory' => 'vendors/',
            'javascripts' => $javascripts,
            'stylesheets' => $styleSheets,
            'others' => $others
        ];
    }

    public function updateWebVendors(string $environment): void
    {
        if (!$this->isCdn && $this->cdnUsed) {
            $this->project->applications[$this->cdnUsed]->updateWebVendors($environment);

            $this->initEntity();

            /** @var Plugin $plugin */
            foreach($this->plugins as $plugin){
                $plugin->updateWebVendors($environment);
            }

            return;
        }

        if (!$this->isCdn) {
            $webVendorsCollection = $this->webVendors([$this]);
        } else {
            $webVendorsCollection = $this->webVendors($this->project->applicationsWithCdn($this->code));
        }

        $webVendorsCollection->update();

        Command::info(sprintf("%s webVendors Updated", $this->displayName()));
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


    public function directory(): string
    {
        return $this->project->directory() . DIRECTORY_SEPARATOR . '_' . $this->code;
    }

    public function directoryControllers(): string
    {
        return $this->directory() . DIRECTORY_SEPARATOR . 'controllers';
    }

    public function directoryViews(): string
    {
        return $this->directory() . DIRECTORY_SEPARATOR . 'views';
    }

    public function directoryWeb(): string
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

    /**
     * @param array $applications
     * @return void
     */
    public function webVendorsFilesCreate(array $applications): void
    {
        if ($applications) {
            $files = ['javascripts' => [], 'stylesheets' => [], 'others' => []];
            foreach ($applications as $application) {
                $applicationFiles = $application->webVendorsFiles();
                $files['javascripts'] += $applicationFiles['javascripts'];
                $files['stylesheets'] += $applicationFiles['stylesheets'];
                $files['others'] += $applicationFiles['others'];
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
            if ($files['others']) {
                $files['others'] = array_unique($files['others']);
                foreach ($files['others'] as $file) {
                    $this->fileCopy(
                        $this->architekt->directoryFilesWebVendors() . DIRECTORY_SEPARATOR . $file,
                        $this->directoryWebVendors() . DIRECTORY_SEPARATOR . $file
                    );
                }
            }
        }
    }

    /**
     * @param Application[] $applications
     */
    public function webVendors(array $applications): WebVendorCollection
    {
        $files = ['javascripts' => [], 'stylesheets' => [], 'others' => []];
        $webVendorCollection = new WebVendorCollection(
            $this->architekt,
            $this->project,
            $this,
            $this->architekt->directoryFilesWebVendors(),
            $this->directoryWebVendors()
        );


        if ($applications) {
            foreach ($applications as $application) {

                $webVendorCollection->add(
                    $this->architekt->json->applicationWebVendors(
                        $this->project->code,
                        $application->code
                    )
                );
            }
        }

        return $webVendorCollection;
    }

    public function addCssClass(string $cssClass, string $bgColor, string $textColor)
    {
        //GARAGE
       /* $colors = [
            'customer' => ['bgColor'=>'83A697','textColor'=>'FFFFFF'],
            'quotation' => ['bgColor'=>'7ab4e1','textColor'=>'FFFFFF'],
            'repairOrder' => ['bgColor'=>'F4661B','textColor'=>'FFFFFF'],
            'bill' => ['bgColor'=>'e8ce45','textColor'=>'000000'],
            'credit' => ['bgColor'=>'c99bb1','textColor'=>'000000'],
            'vehicle' => ['bgColor'=>'B3B191','textColor'=>'FFFFFF'],
            'calendar' => ['bgColor'=>'A5DF00','textColor'=>'FFFFFF'],
            'inventory' => ['bgColor'=>'e66465','textColor'=>'FFFFFF'],
            'accounting' => ['bgColor'=>'97d3cc','textColor'=>'000000'],
        ];*/

        /*$colors = [
            'organization' => ['bgColor'=>'83A697','textColor'=>'FFFFFF'],
            'groups' => ['bgColor'=>'B3B191','textColor'=>'FFFFFF'],
            'person' => ['bgColor'=>'e66465','textColor'=>'FFFFFF'],
            'internal' => ['bgColor'=>'97d3cc','textColor'=>'FFFFFF'],
        ];*/

        $colors = [
            'training' => ['bgColor'=>'4591dc','textColor'=>'FFFFFF'],
            'tool' => ['bgColor'=>'94378a','textColor'=>'FFFFFF'],
            'link' => ['bgColor'=>'dc4583','textColor'=>'FFFFFF'],
            'customer' => ['bgColor'=>'cb318d','textColor'=>'FFFFFF'],
            'homework' => ['bgColor'=>'83A697','textColor'=>'FFFFFF'],
            'quiz' => ['bgColor'=>'c99bb1','textColor'=>'FFFFFF'],
            'email' => ['bgColor'=>'e66465','textColor'=>'FFFFFF'],
            'meeting' => ['bgColor'=>'A5DF00','textColor'=>'FFFFFF'],
        ];
        $content = '';
        foreach($colors as $cssClass=>$color) {

            extract($color);
            $bgColor = '#' . $bgColor;
            $BGRgb = sscanf($bgColor, "#%02x%02x%02x");

            $textColor = '#' . $textColor;

            $template = Color::template($this->architekt);
            $template->assign([
                'CLASS_NAME' => $cssClass,
                'COLOR' => $bgColor,
                'COLOR_RGB' => join(',',$BGRgb),
                'COLOR_D1' => Color::darken($bgColor, 1.1),
                'COLOR_D2' => Color::darken($bgColor, 1.2),
                'COLOR_TXT' => $textColor
            ]);

            $content.= $template->fetch('cssClass.css')."\n";
        }
        file_put_contents('./generate.css' , $content);
        echo 'ok';

        die();

    }
}