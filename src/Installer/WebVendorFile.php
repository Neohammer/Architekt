<?php

namespace Architekt\Installer;

class WebVendorFile
{
    public function __construct(
        public string     $name,
        private string    $type,
        private string    $path,
        private string    $outputPath,
    )
    {

    }

    public function directory(): string
    {
        return $this->path;
    }

    public function source(): string
    {
        return $this->directory() . DIRECTORY_SEPARATOR . $this->name;
    }
}