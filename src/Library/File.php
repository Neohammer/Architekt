<?php

namespace Architekt\Library;

use Architekt\DB\DBEntity;
use Architekt\DB\Entity;
use Architekt\DB\DBEntityCache;
use Gmao\User;
use Gmao\Equipment;
use Gmao\Operation;
use Gmao\PreventivePlanRange;
use Gmao\PreventivePlanRangeOperation;
use Gmao\Subset;
use Gmao\WorkOrder;

class File extends DBEntity
{
    use DBEntityCache;

    private const DIRECTORY_DEPTH = 2;
    private const DIRECTORY_LETTERS = 1;
    public const ACCESS_ADMIN = 0;
    public const ACCESS_CHIEF = 10;
    public const ACCESS_PUBLIC = 20;
    public const TYPE_OPERATION = 'operation';
    public const TYPE_EQUIPMENT = 'equipment';
    public const TYPE_WORK_ORDER = 'workOrder';

    static public array $allowedTypes = [
        self::TYPE_OPERATION,
        self::TYPE_EQUIPMENT,
        self::TYPE_WORK_ORDER
    ];

    static public array $allowedAccesses = [
        self::ACCESS_ADMIN,
        self::ACCESS_CHIEF,
        self::ACCESS_PUBLIC
    ];

    private static bool $transactionStarted = false;
    private static array $transactionFiles;
    protected static ?string $_table = 'file';

    public static function preventivePlanRangeList(PreventivePlanRange $preventivePlanRange): array
    {
        $that = new self;
        $operation = new Operation();

        $that->_search()
            ->join($operation, $operation->_primaryKey(), $that, 'external_id')
            ->filterOn('external_type', self::TYPE_OPERATION)
            ->join(new PreventivePlanRangeOperation(), $operation->_strangerKey(), $operation);

        return $that->_results();
    }

    public static function workOrderList(WorkOrder $workOrder): array
    {
        $that = new self;
        $that->_search()->filter([
            'external_type' => File::TYPE_WORK_ORDER,
            'external_id' => $workOrder
        ]);

        return $that->_results();
    }

    public static function operationList(Operation $operation)
    {
        $that = new self;
        $that->_search()->filter([
            'external_type' => self::TYPE_OPERATION,
            'external_id' => $operation->_primary()
        ]);

        return $that->_results();
    }

    /**
     * @return static[]
     */
    public static function equipmentList(Equipment $equipment): array
    {
        $that = new self;
        $that->_search()->filter($equipment);

        return $that->_results();
    }

    /**
     * @return static[]
     */
    public static function subsetList(Subset $subset): array
    {
        $that = new self;
        $that->_search()->filter($subset);

        return $that->_results();
    }

    public static function createFromContent(string $content, string $extension, int $sourceId): ?self
    {
        $hash = md5($content);
        $path = File . phpself::getBasePath();
        $directory = self::getRelativePath($hash);
        $filename = $path . '/' . $hash;

        if (file_put_contents($filename, $content)) {

            if (self::$transactionStarted) {
                self::$transactionFiles[] = $filename;
            }

            $file = new self();
            $file->_set([
                'source_id' => $sourceId,
                'hash' => $hash,
                'type' => $extension,
                'directory' => $directory,
            ])->_save();

            return $file;
        }

        return null;
    }

    public static function upload(UploadFile $uploadFile, ?File $file = null): ?self
    {
        $fileHash = md5_file($uploadFile->temporaryName());
        $fileUniq = md5(File . phptime());

        $fileRelativePath = static::getRelativePath($fileUniq);
        $filename = File . phpself::getBasePath() . $fileRelativePath . DIRECTORY_SEPARATOR . $fileUniq;
        $move = @move_uploaded_file(
            $uploadFile->temporaryName(),
            $filename
        );

        if (false === $move) {
            return null;
        }
        if (self::$transactionStarted) {
            self::$transactionFiles[] = $filename;
        }

        if (null === $file) {
            $file = new self();
        }
        $file->_set([
            'uniqid' => $fileUniq,
            'hash' => $fileHash,
            'mime_type' => $uploadFile->mimeType(),
            'name' => $uploadFile->filename(),
            'size' => $uploadFile->size(),
            'directory' => $fileRelativePath . DIRECTORY_SEPARATOR

        ]);

        return $file;
    }

    public static function import(string $filePath, ?File $file = null, $deleteFile = false): ?self
    {
        $fileHash = md5_file($filePath);
        $fileUniq = md5(File . phptime());

        $fileRelativePath = static::getRelativePath($fileUniq);
        $filename = File . phpself::getBasePath() . $fileRelativePath . DIRECTORY_SEPARATOR . $fileUniq;

        $move = copy($filePath,$filename);
        if (false === $move) {
            return null;
        }
        \Architekt\Logger::info($filePath.' > '.$filename);

        if (self::$transactionStarted) {
            self::$transactionFiles[] = $filename;
        }

        if (null === $file) {
            $file = new self();
        }

        $originalFileInfo = pathinfo($filePath);
        $file->_set([
            'uniqid' => $fileUniq,
            'hash' => $fileHash,
            'mime_type' => mime_content_type($filename),
            'name' => $originalFileInfo['basename'],
            'size' => filesize($filename),
            'directory' => $fileRelativePath . DIRECTORY_SEPARATOR

        ]);

        if($deleteFile){
            unlink($filePath);
        }

        return $file;
    }

    public static function download(string $url, int $sourceId): ?self
    {
        $content = file_get_contents($url);

        $parts = explode('.', $url);
        $extension = $parts[sizeof($parts) - 1];

        if (false !== $content) {
            return self::createFromContent($content, $extension, $sourceId);
        }

        return null;
    }

    public static function transactionStart(): void
    {
        self::$transactionStarted = true;
        self::$transactionFiles = [];
    }

    public static function transactionCommit(): void
    {
        self::$transactionStarted = false;
        self::$transactionFiles = [];
    }

    public static function transactionRollback(): void
    {
        if (true === self::$transactionStarted) {
            self::$transactionStarted = false;
            foreach (self::$transactionFiles as $file) {
                @unlink($file);
            }
            self::$transactionFiles = [];
        }
    }

    public static function accessLabels(): array
    {
        return [
            self::ACCESS_PUBLIC => 'Public',
            self::ACCESS_CHIEF => 'Chef d\'équipe',
            self::ACCESS_ADMIN => 'Administrateur',
        ];
    }

    public static function defaultAccess(): string
    {
        return self::ACCESS_ADMIN;
    }

    public function userHasAccess(User $user): bool
    {
        if ($user->isGmaoAdmin()) {
            return true;
        }

        if ($user->isTeamLeader() && $this->_get('access') >= self::ACCESS_CHIEF) {
            return true;
        }

        return $this->_get('access') >= self::ACCESS_PUBLIC;
    }

    public function accessLabel(): string
    {
        return self::accessLabels()[$this->_get('access')];
    }

    public function label(): string
    {
        return $this->_get('title');
    }

    public function author(): User
    {
        return User::fromCache($this->_get('author_id'));
    }

    public function category(): FileCategory
    {
        return FileCategory::fromCache($this->_get('file_category_id'));
    }

    public function equipment(): Equipment
    {
        return Equipment::fromCache($this->_get('equipment_id'));
    }

    public function subset(): Subset
    {
        return Subset::fromCache($this->_get('subset_id'));
    }

    public function external(): ?Entity
    {
        switch ($this->_get('external_type')) {
            case self::TYPE_OPERATION:
                return Operation::fromCache($this->_get('external_id'));
                break;
            case self::TYPE_WORK_ORDER:
                return WorkOrder::fromCache($this->_get('external_id'));
                break;
            case 'equipment':
                $equipment = Equipment::fromCache($this->_get('external_id'));

                return $equipment->_isLoaded() ? $equipment : (new Equipment())->_set('registration_number', 'N/A');
        }

        return null;
    }

    public function isImage(): bool
    {
        return in_array($this->_get('mime_type'), [
            'image/jpeg',
            'image/png',
            'image/gif',
        ]);
    }

    public function headers(): self
    {
        header('Content-type: ' . $this->_get('mime_type'));

        return $this;
    }

    public function downloadHeaders(): self
    {
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $this->_get('name') . "\"");

        return $this;
    }

    public function read(): string
    {
        return readfile(
            File . phpself::getBasePath()
            . DIRECTORY_SEPARATOR
            . self::_get('uniqid'));
    }

    public function _delete(): bool
    {
        return $this->unlink() && parent::_delete();
    }

    private function unlink(): bool
    {
        $file = File . phpself::getBasePath()
            . self::getRelativePath($this->_get('hash'))
            . $this->_get('hash');

        if (file_exists($file)) return unlink($file);

        return true;
    }

    private static function getRelativePath(string $hash): string
    {
        $path = '';
        for ($i = 0; $i < self::DIRECTORY_DEPTH; $i++) {
            $path .= DIRECTORY_SEPARATOR
                . substr(
                    $hash,
                    (self::DIRECTORY_LETTERS * $i),
                    self::DIRECTORY_LETTERS + (self::DIRECTORY_LETTERS * $i)
                );
            if (!is_dir(File . phpself::getBasePath())) {
                mkdir(File . phpself::getBasePath(), 07777);
            }
        }
        return $path;
    }

    private static function getBasePath(): string
    {
        return PATH_FILER . 'Library';
    }
}