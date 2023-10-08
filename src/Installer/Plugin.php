<?php

namespace Architekt\Installer;

use Architekt\Controller;
use Architekt\DB\DBConnexion;
use Architekt\DB\DBDatatable;
use Architekt\DB\DBDatatableColumn;

class Plugin
{
    private ?\Architekt\Plugin $pluginEntity;

    /** @var Controller[] */
    private array $controllers;

    const DIRECTORY_PROJECT = 'project';
    const DIRECTORY_APPLICATION = 'application';
    const INSTALLER_APPLICATION = 'applicationInstaller.php';
    const JSON_CONTROLLERS = 'controllers.json';
    const REQUESTS_APPLICATION = 'requests.json';
    const INSTALLER_DATATABLE_RECORDS = 'datatableRecordsInstaller.php';
    const INSTALLER_DATATABLES = 'datatablesInstaller.php';
    const INSTALLER_CONTROLLERS = 'controllersInstaller.php';
    const SETTINGS_BUILDER = 'buildSettings.php';


    use DirectoryTrait;

    public bool $fileReplace;
    protected bool $directoryReplace;

    /**
     * @return DBDatatable[]
     */
    public static function datatablesRequired(): array
    {
        $datatables = [];


        $datatables[] = (new DBDatatable('controller'))
            ->addColumn(DBDatatableColumn::buildAutoincrement())
            ->addColumn(DBDatatableColumn::buildInt('plugin_id', 5))
            ->addColumn(DBDatatableColumn::buildInt('application_id', 5))
            ->addColumn(DBDatatableColumn::buildString('name', 100))
            ->addColumn(DBDatatableColumn::buildString('name_system', 50))
            ->addColumn(DBDatatableColumn::buildString('settings', 1000)->setDefault(null));

        $datatables[] = (new DBDatatable('plugin'))
            ->addColumn(DBDatatableColumn::buildAutoincrement())
            ->addColumn(DBDatatableColumn::buildInt('application_id', 5))
            ->addColumn(DBDatatableColumn::buildString('name', 100))
            ->addColumn(DBDatatableColumn::buildString('name_system', 50));

        return $datatables;

    }


    public function __construct(
        private Architekt   $architekt,
        private Project     $project,
        private Application $application,
        public string       $code
    )
    {
        $this->pluginEntity = null;
        $this->controllers = [];

        $this->fileReplace = false;
        $this->directoryReplace = false;

        $this->build();
    }

    private function displayName(): string
    {
        return sprintf(
            '%s:%s:%s -',
            $this->project->code,
            $this->application->code,
            $this->code
        );
    }

    public static function init(Architekt $architekt, Project $project, Application $application, string $code): static
    {
        return new self($architekt, $project, $application, $code);
    }

    public function initEntity(): void
    {
        $this->pluginEntity = \Architekt\Plugin::byNameSystem($this->application->entity(), $this->code);
        if ($this->pluginEntity) {
            Command::warning(sprintf('%s plugin already exists in datatable', $this->displayName()));
        } else {
            $this->pluginEntity = (new \Architekt\Plugin())
                ->_set([
                    $this->application->entity(),
                    'name' => $this->code,
                    'name_system' => $this->code,
                ]);

            $this->pluginEntity->_save();
            Command::info(sprintf('%s plugin n°%d created', $this->displayName(), $this->pluginEntity->_primary()));
        }
    }

    public function entity(): \Architekt\Plugin
    {
        return $this->pluginEntity;
    }

    public function build(): void
    {
        $this->controllers = [];
    }

    public function install(): void
    {
        $this->datatablesCreate();

        $this->initEntity();

        $this->fileReplace = true;

        $baseDirectory = $this->directoryFiles();

        if (is_dir($projectDirectory = $baseDirectory . self::DIRECTORY_PROJECT)) {
            Command::info(sprintf('%s Installing project files', $this->displayName()));
            $this->project->directoryRead($projectDirectory);
        }

        if (file_exists($datatablesInstaller = $baseDirectory . self::INSTALLER_DATATABLES)) {
            Command::info(sprintf('%s Custom datatabases installation', $this->displayName()));

            $datatablesToInstall = [];

            require($datatablesInstaller);

            if ($datatablesToInstall) {
                foreach ($datatablesToInstall as $datatable) {
                    DBConnexion::get()->datatableCreate($datatable);
                }
            }
        }

        if (is_file($datatableRecordsInstaller = $baseDirectory . self::INSTALLER_DATATABLE_RECORDS)) {
            Command::info(sprintf('%s Custom datatable records installer found', $this->displayName()));
            require($datatableRecordsInstaller);
        }

        $allowControllersJsonParse = true;
        $controllersToInstall = [];
        if (file_exists($installerController = $baseDirectory . self::INSTALLER_CONTROLLERS)) {
            $allowControllersJsonParse = false;

            require($installerController);
        }

        if ($allowControllersJsonParse && file_exists($controllersJson = $baseDirectory . self::JSON_CONTROLLERS)) {
            $controllers = json_decode(file_get_contents($controllersJson), true);

            foreach ($controllers as $controllerCode => $controller) {

                $type = $controller['type'] ?? null;
                if ($type === "administration" && !$this->application->isAdmin) {
                    continue;
                }
                $controllersToInstall[$controllerCode] = $controller;
            }
        }

        if ($controllersToInstall) {
            echo implode(', ',array_keys($controllersToInstall))."\n";
            foreach ($controllersToInstall as $controllerCode => $controller) {
                $this->installController($controllerCode, $controller);
            }
        }


        $allowApplicationDirectoryCopy = true;
        if (is_file($applicationInstaller = $baseDirectory . self::INSTALLER_APPLICATION)) {
            Command::info(sprintf('%s Custom installer found for applications', $this->displayName()));
            $allowApplicationDirectoryCopy = false;

            require($applicationInstaller);
        }

        if ($allowApplicationDirectoryCopy && is_dir($applicationDirectory = $baseDirectory . self::DIRECTORY_APPLICATION)) {
            Command::info(sprintf('%s Installing application files', $this->displayName()));
            $this->directoryRead($applicationDirectory);
        }

        if (file_exists($requestsJson = $baseDirectory . self::REQUESTS_APPLICATION)) {
            Command::info(sprintf('%s Executing requests', $this->displayName()));

            $requests = json_decode(file_get_contents($requestsJson), true);

            if (array_key_exists('datatables', $requests)) {
                foreach ($requests['datatables'] as $datatableArray) {
                    DBConnexion::get()->datatableCreate(
                        DBDatatable::fromArray($datatableArray)
                    );
                }
            }
        }
    }

    public function buildSettings(): void
    {
        $baseDirectory = $this->directoryFiles();

        if (file_exists($settingBuilder = $baseDirectory . self::SETTINGS_BUILDER)) {
            Command::info(sprintf('%s Build settings', $this->displayName()));

            require($settingBuilder);
        }
    }

    public function installController(string $controllerCode, array $controllerSettings = []): void
    {
        Command::info(sprintf('%s Install %s', $this->displayName(), $controllerCode));
        $controllerEntity = Controller::byNameSystem($this->entity(), $controllerCode);
        if ($controllerEntity) {
            Command::warning(sprintf('%s plugin controller %s already exists in datatable', $this->displayName(), $controllerCode));
        } else {
            $controllerEntity = (new Controller())
                ->_set([
                    $this->entity(),
                    $this->application->entity(),
                    'name' => $controllerSettings['name'] ?? $controllerCode,
                    'name_system' => $controllerCode,
                    'settings' => json_encode($this->parseSettings($controllerSettings['settings'] ?? []))
                ]);

            $controllerEntity->_save();

            Command::info(sprintf('%s plugin controller n°%d (%s) created', $this->displayName(), $controllerEntity->_primary(), $controllerCode));
        }
        $this->addController($controllerEntity);
    }

    public function addController(Controller $controller): static
    {
        $this->controllers[$controller->_get('name_system')] = $controller;

        return $this;
    }

    private function datatablesCreate(): void
    {
        $connexion = DBConnexion::get();

        foreach (self::datatablesRequired() as $datatable) {
            if ($connexion->datatableExists($datatable)) {
                Command::warning(sprintf('%s - Datatable %s already exists', $this->displayName(), $datatable->name()));
                continue;
            }
            $connexion->datatableCreate($datatable);
            Command::info(sprintf('%s - Datatable %s created', $this->displayName(), $datatable->name()));

        }
    }

    private function parseSettings(array $settings): array
    {
        if ($type = $this->application->type()) {
            if (array_key_exists($type, $settings)) {
                return $settings[$type];
            }
        }
        if (array_key_exists('default', $settings)) {
            return $settings['default'];
        }

        return [];
    }

    public function template(): Template
    {
        return (new Template())
            ->setCompileDir($this->architekt->directoryTemporary())
            ->setTemplateDir($this->architekt->directoryPlugins() . DIRECTORY_SEPARATOR . $this->entity()->_get('name_system'))
            ->assign($this->project->templateVars())
            ->assign($this->application->templateVars())
            ->assign($this->templateVars());
    }

    private function templateVars(): array
    {
        return [
            'PLUGIN' => $this->pluginEntity,
            'CONTROLLERS' => $this->controllers,
        ];
    }

    private function directory(): string
    {
        return $this->application->directory();
    }

    private function directoryFiles(): string
    {
        return $this->architekt->directoryPlugins() . DIRECTORY_SEPARATOR . $this->code . DIRECTORY_SEPARATOR;
    }

}