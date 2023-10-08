<?php

namespace Architekt\Utility;

use Architekt\Application;
use Architekt\Controller;
use Architekt\DB\DBEntity;
use Architekt\DB\Entity;

class ControllerSettings implements SettingsInterface
{

    use SettingsTrait;

    public function __construct(private Controller $controller){}

    public function decodeValues(): array
    {
        if ($this->controller->_get('settings')) {
            return json_decode($this->controller->_get('settings'), true);
        }

        return [];
    }

    public function encodeValues(array $values): static
    {
        $this->controller->_set('settings', json_encode($values));

        return $this;
    }
}