<?php

namespace Architekt\Utility;

use Architekt\Application;
use Architekt\DB\Entity;

class Settings extends Entity
{
    /**
     * @var static[]
     */
    private static array $cache = [];

    protected static ?string $_table = 'settings';

    public static function byApp(?string $app = null): static
    {
        $app = $app ?? Application::$configurator->get('name');

        if(!array_key_exists($app , self::$cache)){

            $that = new self;
            $that->_search()->filter('app', $app);

            if (!$that->_next()) {
                $that->_set('app', $app)->_save();
            }
            self::$cache[$app] = clone $that;
        }

        return self::$cache[$app];
    }

    public function is(string $module, string $key): bool
    {
        $values = $this->decodeValues();
        if (!array_key_exists($module, $values)) {
            return false;
        }

        if (!array_key_exists($key, $values[$module])) {
            return false;
        }

        return $values[$module][$key] === true;
    }

    public function setValue(string $module, string $key, int|string|bool $value): static
    {
        $values = $this->decodeValues();

        if (!array_key_exists($module, $values)) {
            $values[$module] = [];
        }
        $values[$module][$key] = $value;

        $this->encodeValues($values);

        return $this;
    }

    private function decodeValues(): array
    {
        if ($this->_get('values')) {
            return json_decode($this->_get('values'), true);
        }

        return [];
    }

    private function encodeValues(array $values): static
    {
        $this->_set('values', json_encode($values));

        return $this;
    }
}