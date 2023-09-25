<?php

namespace Architekt\Installer;

trait DirectoryTrait
{
    private function directoriesCreate(): self
    {
        foreach($this->directories as $directory){
            $this->directoryCreate($directory);
        }

        return $this;
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
            if ($this->fileReplace || !file_exists($outputFile)) {
                if (file_exists($outputFile)) unlink($outputFile);
                copy($inputFile, $outputFile);
            }
        }

        return $this;
    }


    private function fileCreate(string $file, Template $template, string $fileTemplate): static
    {
        if ($this->fileReplace || !file_exists($file)) {
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

    private function fileCopy(string $fileFrom, string $fileTo): static
    {
        if ($this->fileReplace || !file_exists($fileTo)) {
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
}