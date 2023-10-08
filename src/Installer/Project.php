<?php

namespace Architekt\Installer;

use Architekt\DB\DBConnexion;
use Architekt\DB\DBDatabase;
use Architekt\DB\DBDatatable;
use Architekt\DB\DBDatatableColumn;
use Architekt\DB\DBRecordRow;

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
    public ?\Architekt\Project $projectRow;

    /**
     * @return DBDatatable[]
     */
    public static function datatablesRequired(): array
    {
        $datatables = [];

        $datatables[] = (new DBDatatable('project'))
            ->addColumn(DBDatatableColumn::buildAutoincrement())
            ->addColumn(DBDatatableColumn::buildString('name', 100))
            ->addColumn(DBDatatableColumn::buildString('name_system', 50));

        return $datatables;

    }

    public static function datatablesRequiredToJson(string $outputDir): void
    {
        $datatables = self::datatablesRequired();
        foreach($datatables as $k=>$v){
            $datatables[$k] = $v->toArray();
        }

        file_put_contents(
            $file = $outputDir.DIRECTORY_SEPARATOR.'datatables.json' ,
            json_encode($datatables)
        );
        
        Command::info(sprintf('json project datatables generated'));

    }


    private function __construct(Architekt $architekt, string $code)
    {
        $this->architekt = $architekt;
        $this->code = $code;
        $this->applications = [];
        $this->directories = [];
        $this->projectRow = null;

        $this->directoryReplace = false;
        $this->fileReplace = false;

        $this->build();
    }

    public static function init(Architekt $architekt, string $code): static
    {
        return new self($architekt, $code);
    }

    public function install(string $environment): void
    {
        Command::info(sprintf('%s - Install %s', $this->code, $environment));
        $this->directoriesCreate();
        $this->fileReplace = true;
        $this->filesCreate();

        if($this->hasDatabase()){
            $this->databaseCreate($environment);

            $this->databaseConnect($environment);
            $this->datatablesCreate($environment);
        }

        $this->projectRow = \Architekt\Project::byNameSystem($this->code);
        if($this->projectRow){
            Command::warning(sprintf('%s already exists in project datatable',$this->code));
        }
        else{
            $this->projectRow = (new \Architekt\Project())
                ->_set([
                    'name' => $this->code,
                    'name_system' => $this->code,
                ]);

            $this->projectRow->_save();
            Command::info(sprintf('%s project n°%d created',$this->code,$this->projectRow->_primary()));
        }


        foreach ($this->applications as $application) {
            $application->install($environment);
        }


        foreach ($this->applications as $application) {
            $application->buildSettings();
        }

    }

    public function databaseConnect(string $environment = 'local'): void
    {
        if ($databaseCode = $this->database()) {
            $databaseInfos = $this->architekt->database($environment, $databaseCode);

            DBConnexion::add(
                'main',
                $databaseInfos['engine'],
                $databaseInfos['host'],
                $databaseInfos['user'],
                $databaseInfos['password'],
                $databaseInfos['name'],
            );
        }
    }

    private function hasDatabase(): bool
    {
        return $this->database();
    }

    public function databaseInfos(string $environment): ?array
    {
        if ($databaseCode = $this->database()) {
            return $this->architekt->database($environment, $databaseCode);
        }

        return null;
    }

    private function databaseCreate(string $environment): ?array
    {
        $databaseInfos = $this->databaseInfos($environment);
        if (!$databaseInfos) {
            Command::info(sprintf('%s - Database infos not found, please check json file', $this->code));
            return null;
        }

        DBConnexion::add(
            'architekt',
            $databaseInfos['engine'],
            $databaseInfos['host'],
            $databaseInfos['user'],
            $databaseInfos['password'],
        );

        $database = new DBDatabase($databaseInfos['name']);

        if (DBConnexion::get('architekt')->databaseExists($database)) {
            Command::info(sprintf('%s - Database %s exists', $this->code, $databaseInfos['name']));
            return $databaseInfos;
        }

        if (DBConnexion::get('architekt')->databaseCreate($database)) {
            Command::info(sprintf('%s - Database %s created', $this->code, $databaseInfos['name']));
            return $databaseInfos;
        }

        Command::error(sprintf('%s - Fail to create database %s', $this->code, $databaseInfos['name']));

        if ($prefix = $databaseInfos['prefix'] ?? '') {
            !defined('ARCHITEKT_DATATABLE_PREFIX') && define('ARCHITEKT_DATATABLE_PREFIX' , $prefix);
        }

        return null;
    }


    private function datatablesCreate(string $environment): void
    {

        $connexion = DBConnexion::get();
        $databaseInfos = $this->databaseInfos($environment);

        foreach(self::datatablesRequired() as $datatable){

            $prefix = $databaseInfos['prefix'] ?? '';
            if($prefix){
                $datatable->prefix($prefix);
            }
            if($connexion->datatableExists($datatable)){
                Command::warning(sprintf('%s - Datatable %s already exists', $this->code, $datatable->name()));
                continue;
            }
            $connexion->datatableCreate($datatable);
            Command::info(sprintf('%s - Datatable %s created', $this->code, $datatable->name()));

        }

    }

    public function build(): void
    {
        Command::info(sprintf('%s - build', $this->code));
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


    public function templateVars(): array
    {
        return [
            'PATH_CLASSES' => $this->nameClasses(),
            'PROJECT_CODE' => $this->code,
            'PROJECT_CAMEL' => $this->architekt->toCamelCase($this->code),
            'PROJECT_NAME' => $name = ($this->architekt->json->project($this->code)['name'] ?? 'NoName'),
            'PROJECT_NAME_CAMEL' => $this->architekt->toCamelCase($name),
            'APPLICATIONS_DOMAINS_BY_ENVIRONMENT' => $this->domainsByEnvironment(),
            'DATATABLE_PREFIX' => $this->databaseInfos('local')['prefix'] ?? null,
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

        foreach ($this->environments() as $environment) {
            $databaseInfos = null;
            if ($databaseCode = $this->database()) {
                $databaseInfos = $this->architekt->database($environment, $databaseCode);
            }


            $this->fileCreate(
                $this->directoryEnvironment() . DIRECTORY_SEPARATOR . sprintf('config.%s.php', $environment),
                $template
                    ->assign('ENVIRONMENT', $environment)
                    ->assign('APPLICATIONS_URLS_PRIMARY', $this->primaryUrls($environment))
                    ->assign('DATABASE', $databaseInfos)
                ,
                'config.environment.php.tpl'
            );
        }

    }


    public function database(): ?string
    {
        return $this->architekt->json->project($this->code)['database'] ?? null;
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
                    if (!array_key_exists($environment, $domains)) {
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

    /**
     * @return Application[]
     */
    public function applicationsWithAdministration(string $administrationCode): array
    {
        $applications = [];
        foreach ($this->applications as $application) {
            if ($application->administration === $administrationCode) {
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
        return 'classes';
    }

    public function directoryClassesUsers(): string
    {
        return $this->directoryClasses() . DIRECTORY_SEPARATOR . 'Users';
    }

    public function directoryClassesControllers(): string
    {
        return $this->directoryClasses() . DIRECTORY_SEPARATOR . 'Controllers';
    }

    public function directoryClassesEvents(): string
    {
        return $this->directoryClasses() . DIRECTORY_SEPARATOR . 'Events';
    }

    public function directoryEnvironment(): string
    {
        return $this->directory() . DIRECTORY_SEPARATOR . 'environments';
    }
}