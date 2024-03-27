<?php

namespace Architekt\Library;

use Architekt\DB\DBEntity;
use Architekt\DB\DBEntityCache;
use Architekt\DB\Entity;
use Gmao\Equipment;
use Gmao\Operation;
use Gmao\PreventivePlanRange;
use Gmao\PreventivePlanRangeOperation;
use Gmao\Subset;
use Gmao\User;
use Gmao\WorkOrder;

if (!defined('ARCHITEKT_DATATABLE_PREFIX')) {
    define('ARCHITEKT_DATATABLE_PREFIX', 'at_');
}

class File extends DBEntity
{
    use DBEntityCache;

    private const DIRECTORY_DEPTH = 2;
    private const DIRECTORY_LETTERS = 1;

    private static bool $transactionStarted = false;
    private static array $transactionFiles;

    protected static ?string $_table_prefix = ARCHITEKT_DATATABLE_PREFIX;
    protected static ?string $_table = 'file';

    public static function createFromUrl(string $url, string $name, ?File $file = null): ?self
    {
        $fileUniq = md5(uniqid() . time());
        $fileRelativePath = static::getRelativePath($fileUniq);
        $filePath = self::getBasePath() . $fileRelativePath . DIRECTORY_SEPARATOR . $fileUniq;

        if (file_put_contents($filePath, file_get_contents($url))) {

            $fileHash = md5_file($filePath);
            if (self::$transactionStarted) {
                self::$transactionFiles[] = $filePath;
            }

            if (!$file) {
                $file = new self();
            }
            $file->_set([
                'uniqid' => $fileUniq,
                'hash' => $fileHash,
                'mime_type' => mime_content_type($filePath),
                'name' => $name,
                'size' => filesize($filePath),
                'directory' => $fileRelativePath . DIRECTORY_SEPARATOR
            ])->_save();

            return $file;
        }

        return null;
    }


    public static function createFromString(string $string, string $name, ?File $file = null): ?self
    {
        $fileUniq = md5(uniqid() . time());
        $fileRelativePath = static::getRelativePath($fileUniq);
        $filePath = self::getBasePath() . $fileRelativePath . DIRECTORY_SEPARATOR . $fileUniq;

        if (file_put_contents($filePath, $string)) {

            $fileHash = md5_file($filePath);
            if (self::$transactionStarted) {
                self::$transactionFiles[] = $filePath;
            }

            if (!$file) {
                $file = new self();
            }
            $file->_set([
                'uniqid' => $fileUniq,
                'hash' => $fileHash,
                'mime_type' => mime_content_type($filePath),
                'name' => $name,
                'size' => filesize($filePath),
                'directory' => $fileRelativePath . DIRECTORY_SEPARATOR
            ])->_save();

            return $file;
        }

        return null;
    }




    public static function upload(UploadFile $uploadFile, ?File $file = null): ?self
    {
        $fileHash = md5_file($uploadFile->temporaryName());
        $fileUniq = md5(uniqid() . time());

        $fileRelativePath = static::getRelativePath($fileUniq);
        $filePath = self::getBasePath() . $fileRelativePath . DIRECTORY_SEPARATOR . $fileUniq;
        $move = @move_uploaded_file(
            $uploadFile->temporaryName(),
            $filePath
        );

        if (false === $move) {
            return null;
        }
        if (self::$transactionStarted) {
            self::$transactionFiles[] = $filePath;
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
        $fileUniq = md5(uniqid() . time());

        $fileRelativePath = static::getRelativePath($fileUniq);
        $filename = self::getBasePath() . DIRECTORY_SEPARATOR . $fileRelativePath . DIRECTORY_SEPARATOR . $fileUniq;

        $move = copy($filePath, $filename);
        if (false === $move) {
            return null;
        }
        \Architekt\Logger::info($filePath . ' > ' . $filename);

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

        if ($deleteFile) {
            unlink($filePath);
        }

        return $file;
    }

    public static function download(string $url, ?self $file = null): ?self
    {
        $parts = explode('.', $url);
        $filename = $parts[sizeof($parts) - 2];

        return self::createFromUrl($url, $filename, $file);
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

    public function label(): string
    {
        return $this->_get('title');
    }

    public function uniqid(): string
    {
        return $this->_get('uniqid');
    }

    public function datetime(): string
    {
        return $this->_get('datetime');
    }

    public function datetimeChange(): string
    {
        return $this->_get('datetime_change');
    }

    public function isSame(File $compare): bool
    {
        return $this->uniqid() === $compare->uniqid();
    }

    public function isOlderThan(File $compare , bool $strict = false): bool
    {
        return
            strtotime($this->_get('datetime_change')) < strtotime($compare->_get('datetime_change'))
            &&
            ($strict || strtotime($this->_get('datetime_change')) === strtotime($compare->_get('datetime_change')));
    }

    public function author(): User
    {
        return User::fromCache($this->_get('author_id'));
    }

    public function base64(): string
    {
        return sprintf(
            'data:%s;base64,%s',
            $this->_get('mime_type'),
            $this->base64Content()
        );
    }

    public function base64Content(): string
    {
        return base64_encode(file_get_contents($this->filePath()));
    }

    public function isImage(): bool
    {
        return in_array($this->_get('mime_type'), [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ]);
    }

    public function isMusic(): bool
    {
        return in_array($this->_get('mime_type'), [
            'audio/mpeg',
        ]);
    }

    public function isVideo(): bool
    {
        return in_array($this->_get('mime_type'), [
            'video/mp4',
        ]);
    }

    public function isPdf(): bool
    {
        return $this->_get('mime_type') === 'application/pdf';
    }

    public function canBeDisplay(): bool
    {
        return $this->isImage() || $this->isMusic() || $this->isVideo() || $this->isPdf();
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
        return readfile($this->filePath());
    }

    public function content(): string
    {
        return file_get_contents($this->filePath());
    }


    public function _delete(): bool
    {
        return $this->unlink() && parent::_delete();
    }

    private function unlink(): bool
    {
        $file = self::getBasePath()
            . self::getRelativePath($this->_get('hash'))
            . $this->_get('hash');

        if (file_exists($file)) return unlink($file);

        return true;
    }

    private static function getRelativePath(string $hash): string
    {
        if (!is_dir(self::getBasePath())) {
            mkdir(self::getBasePath(), 07777);
        }
        $path = '';
        for ($i = 0; $i < self::DIRECTORY_DEPTH; $i++) {
            $path .= DIRECTORY_SEPARATOR
                . substr(
                    $hash,
                    (self::DIRECTORY_LETTERS * $i),
                    self::DIRECTORY_LETTERS + (self::DIRECTORY_LETTERS * $i)
                );

            if (!is_dir(self::getBasePath() . DIRECTORY_SEPARATOR . $path)) {
                mkdir(self::getBasePath() . DIRECTORY_SEPARATOR . $path, 07777);
            }
        }
        return $path;
    }

    private static function getBasePath(): string
    {
        return PATH_FILER . DIRECTORY_SEPARATOR . 'Library';
    }

    public function filePath(): string
    {
        return
            self::getBasePath() . DIRECTORY_SEPARATOR .
            //$this->_get('privacy'). DIRECTORY_SEPARATOR.
            static::getRelativePath($this->_get('uniqid')) . DIRECTORY_SEPARATOR .
            $this->_get('uniqid');
    }
}