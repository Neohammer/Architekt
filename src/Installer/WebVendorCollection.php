<?php

namespace Architekt\Installer;

use Architekt\Installer\Json\WebVendorsJson;

class WebVendorCollection
{
    use DirectoryTrait;

    protected bool $fileReplace;

    protected bool $directoryReplace;

    private WebVendorsJson $json;

    /** @var WebVendor[] $webVendors */
    private array $webVendors;

    public function __construct(
        private Architekt   $architekt,
        private Project     $project,
        private Application $application,
        public              $inputPath,
        public              $outputPath
    )
    {
        $this->fileReplace = false;
        $this->directoryReplace = false;
        $this->json = WebVendorsJson::init($inputPath);
        $this->webVendors = [];
    }

    public function add(?array $applicationWebVendors)
    {
        if (!$applicationWebVendors) {
            return;
        }
        $applicationWebVendors = $this->json->addPrerequisites(array_merge(
            ['architekt'],
            $applicationWebVendors ?? []
        ));

        foreach ($applicationWebVendors as $applicationWebVendor) {
            $this->webVendors[$applicationWebVendor] = new WebVendor(
                $applicationWebVendor,
                $this->inputPath,
                $this->outputPath,
                $this->json->webVendor($applicationWebVendor)
            );
        }
    }

    /** @return WebVendorFile[] */
    public function files(): array
    {
        $files = [];

        /** @var WebVendor $webVendor */
        foreach ($this->webVendors as $webVendor) {
            $files = array_merge($files, $webVendor->files());
        }

        return $files;
    }

    public function update(): void
    {
        if (!is_dir($this->outputPath)) {
            Command::error(sprintf('%s - Please install before updating', $this->displayName()));
            exit();
        }
        foreach ($this->files() as $file) {

            $outputFile = $this->outputPath . DIRECTORY_SEPARATOR . $file->source();
            if (file_exists($outputFile)) {
                continue;
            }
            Command::info(sprintf('%s - Add %s', $this->displayName(), $file->source()));

            $this
                ->directoryCreate(
                    $this->outputPath . DIRECTORY_SEPARATOR . $file->directory()
                )
                ->fileCopy(
                    $this->inputPath . DIRECTORY_SEPARATOR . $file->source(),
                    $outputFile
                );
        }
    }

    private function displayName()
    {
        return sprintf('%s webVendors - ', $this->application->displayName());
    }

}