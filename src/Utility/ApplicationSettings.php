<?php

namespace Architekt\Utility;

use Architekt\Application;
use Architekt\Http\Environment;

class ApplicationSettings implements SettingsInterface
{

    use SettingsTrait;

    public function __construct(private Application $application)
    {
    }

    private function decodeValues(): array
    {
        if ($this->application->_get('settings')) {
            return json_decode($this->application->_get('settings'), true);
        }

        return [];
    }

    private function encodeValues(array $values): static
    {
        $this->application->_set('settings', json_encode($values));

        return $this;
    }

    public function url(): ?string
    {
        return $this->get('urls', Environment::get());
    }

    public function isAdministration(): bool
    {
        return $this->is('general', 'type', 'administration');
    }

    public function applicationUser(): ?string
    {
        return $this->get('general' , 'appUser' );
    }

    /**
     * @return ?Application[]
     */
    public function administrationApplications(): array
    {
        if (!$this->isAdministration()) {
            return [];
        }

        $applicationsPrimaries = $this->get('administration', 'applications');

        $applications = [Application::get()];

        if (!$applicationsPrimaries) {
            return $applications;
        }


        foreach ($applicationsPrimaries as $applicationsPrimary) {
            $applications[] = Application::fromCache($applicationsPrimary);
        }

        return $applications;
    }
}