<?php

namespace Architekt\Utility;

use Architekt\Application;
use Architekt\Auth\Profile;
use Architekt\Controller;

class ProfileSettings
{
    private Application $application;

    public function __construct(
        private Profile $profile,
        ?Application    $application = null
    )
    {
        $this->application = $application ?? Application::get();
    }

    public function decodeValues(): array
    {
        if ($this->profile->_get('settings')) {
            return json_decode($this->profile->_get('settings'), true);
        }

        return [];
    }

    public function encodeValues(array $values): static
    {
        $this->profile->_set('settings', json_encode($values));

        return $this;
    }

    public function is(string|Controller $controllerCode, string $code, string $subCode, bool|string|int $value = true): bool
    {
        $values = $this->decodeValues();

        if ($controllerCode instanceof Controller) {
            $controllerCode = $controllerCode->_get('name_system');
        }

        $valueSet = $values[$controllerCode][$code][$subCode] ?? null;

        if (null !== $valueSet) {
            return $valueSet === $value;
        }

        return Settings::byController(
            Controller::byApplicationAndNameSystem(
                $this->application,
                $controllerCode)
        )
            ->is($code, $subCode, $value);
    }

    public function get(string|Controller $controllerCode, string $code, string $subCode): null|bool|string|int
    {
        $values = $this->decodeValues();

        if ($controllerCode instanceof Controller) {
            $controllerCode = $controllerCode->_get('name_system');
        }

        $valueSet = $values[$controllerCode][$code][$subCode] ?? null;

        if (null !== $valueSet) {
            return $valueSet;
        }

        $controller = $controllerCode;
        if (!$controller instanceof Controller) {
            $controller = Controller::byApplicationAndNameSystem(
                $this->application,
                $controllerCode
            );
        }

        return Settings::byController($controller)->get($code, $subCode);
    }

    public function setValue(string|Controller $controllerCode, string $code, string $subCode, int|string|bool $value): static
    {
        $values = $this->decodeValues();

        if ($controllerCode instanceof Controller) {
            $controllerCode = $controllerCode->_get('name_system');
        }

        $valueSet = $values[$controllerCode][$code][$subCode] ?? null;

        if ($valueSet === null) {
            if (!array_key_exists($controllerCode, $values)) {
                $values[$controllerCode] = [];
            }

            if (!array_key_exists($code, $values[$controllerCode])) {
                $values[$controllerCode][$code] = [];
            }
        }

        $values[$controllerCode][$code][$subCode] = $value;

        $this->encodeValues($values);

        return $this;
    }

    public function unsetValue(string|Controller $controllerCode, string $code, string $subCode): static
    {
        $values = $this->decodeValues();

        if ($controllerCode instanceof Controller) {
            $controllerCode = $controllerCode->_get('name_system');
        }

        $valueSet = $values[$controllerCode][$code][$subCode] ?? null;

        if ($valueSet === null) {
            return $this;
        }

        unset($values[$controllerCode][$code][$subCode]);

        $this->encodeValues($values);

        return $this;
    }
}