<?php

namespace Architekt\Installer;

trait DirectoryTrait
{
    private bool $directoryTraitDebug = false;

    private function directoriesCreate(): self
    {
        foreach ($this->directories as $directory) {
            $this->directoryCreate($directory);
        }

        return $this;
    }

    public function directoryCreate(string $dir): static
    {
        if (!is_dir($dir)) {
            if ($this->directoryTraitDebug) Command::info(sprintf('%s - %s created', $this->code ?? 'architekt', $dir));
            mkdir($dir);
        } else {
            if ($this->directoryTraitDebug) Command::info(sprintf('%s - %s exists', $this->code ?? 'architekt', $dir));
        }

        return $this;
    }

    private function directoryCopy(string $dir, string $dirTo, bool $recursive = false): static
    {
        if ($this->directoryTraitDebug) Command::info(sprintf('%s - %s copy', $this->code ?? 'architekt', $dir));
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
                if (file_exists($outputFile)) {
                    unlink($outputFile);
                }
                copy($inputFile, $outputFile);
            }
        }

        return $this;
    }

    private function directoryRead(string $directory, string $directoryAdd = '', array $templateVars = []): void
    {
        if ($this->directoryTraitDebug) Command::info(sprintf("Reading %s (add:%s)", $directory, $directoryAdd));
        $opendir = opendir($directory);


        while ($file = readdir($opendir)) {
            if (in_array($file, ['.', '..'])) continue;

            $filePath = $directory . DIRECTORY_SEPARATOR . $file;
            if (is_dir($filePath)) {
                $this->directoryCreate(
                    $this->directory() . $directoryAdd . DIRECTORY_SEPARATOR . $file
                );
                $this->directoryRead(
                    $directory . DIRECTORY_SEPARATOR . $file,
                    $directoryAdd . DIRECTORY_SEPARATOR . $file,
                    $templateVars
                );

                continue;
            }

            if (!is_file($filePath)) continue;

            if (substr($file, -4, 4) === '.tpl') {
                $this->fileCreate(
                    $this->directory() . $directoryAdd . DIRECTORY_SEPARATOR . substr($file, 0, -4),
                    $this->template()->assign($templateVars),
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

    public function fileCreate(string $file, Template $template, string $fileTemplate): static
    {
        if ($this->fileReplace || !file_exists($file)) {
            if ($this->directoryTraitDebug) Command::info(sprintf('%s - %s created', $this->code ?? 'architekt', $file));
            try {
                file_put_contents(
                    $file,
                    $template->fetch($fileTemplate)
                );
            } catch (\Error|\Exception $e) {
                print_r(array_keys($this->controllers));
                Command::error(sprintf('%s - %s FAIL', $this->code ?? 'architekt', $file));
                Command::error(sprintf('%s - %s FAIL', $this->code ?? 'architekt',$fileTemplate));
                Command::error($e->getMessage());
                exit();
            }
        } else {
            if ($this->directoryTraitDebug) Command::info(sprintf('%s - %s exists', $this->code ?? 'architekt', $file));
        }

        return $this;
    }

    private function fileCopy(string $fileFrom, string $fileTo): static
    {
        if ($this->fileReplace || !file_exists($fileTo)) {

            if ($this->directoryTraitDebug) Command::info(sprintf('%s - %s exists', $this->code ?? 'architekt', $fileTo));
            if (file_exists($fileTo)) {
                unlink($fileTo);
            }
            copy(
                $fileFrom,
                $fileTo
            );
        } else {
            if ($this->directoryTraitDebug) Command::info(sprintf('%s - %s exists', $this->code ?? 'architekt', $fileTo));
        }

        return $this;
    }
}