<?php

namespace Architekt\Library;

class UploadFile
{
    private string $name;
    private string $type;
    private string $temporaryName;
    private int $error;
    private int $size;

    public function __construct(?array $upload)
    {
        if (null !== $upload) {
            $this->name = $upload['name'];
            $this->type = $upload['type'];
            $this->temporaryName = $upload['tmp_name'];
            $this->error = $upload['error'];
            $this->size = $upload['size'];
        } else {
            $this->error = UPLOAD_ERR_NO_FILE;
        }
    }

    public function isImage(): bool
    {
        return in_array(
            $this->type,
            [
                'image/jpeg',
                'image/png',
            ],
            true
        );
    }

    public function requestUpload(): bool
    {
        return UPLOAD_ERR_NO_FILE !== $this->error;
    }

    public function hasBeenUploaded(): bool
    {
        return UPLOAD_ERR_OK === $this->error;
    }

    public function size(): int
    {
        return $this->size;
    }

    public function temporaryName(): string
    {
        return $this->temporaryName;
    }

    public function mimeType(): string
    {
        return $this->type;
    }

    public function filename(): string
    {
        return $this->name;
    }
}