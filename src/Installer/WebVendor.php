<?php

namespace Architekt\Installer;

class WebVendor
{
    public function __construct(
        public  $name,
        public  $path,
        public  $outputPath,
        private $config
    )
    {
    }

    /** @return string[] */
    public function files(): array
    {
        $files = [];

        if ($this->config['js'] ?? []) {
            if (!is_array($this->config['js'])) {
                $this->config['js'] = [$this->config['js']];
            }
            foreach ($this->config['js'] ?? [] as $javascript) {
                $files[] =  new WebVendorFile($javascript.'.js' , 'js', $this->name, $this->outputPath);
            }
        }

        if ($this->config['css'] ?? []) {
            if (!is_array($this->config['css'])) {
                $this->config['css'] = [$this->config['css']];
            }
            foreach ($this->config['css'] ?? [] as $css) {
                $files[] =  new WebVendorFile($css.'.css', 'css', $this->name, $this->outputPath);
            }
        }

        if ($this->config['other'] ?? []) {
            if (!is_array($this->config['other'])) {
                $this->config['other'] = [$this->config['other']];
            }
            foreach ($this->config['other'] ?? [] as $other) {
                $files[] =  new WebVendorFile($other,'other', $this->name, $this->outputPath);
            }
        }


        return $files;
    }


}