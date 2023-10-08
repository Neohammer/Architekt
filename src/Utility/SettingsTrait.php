<?php

namespace Architekt\Utility;

use Architekt\DB\Exceptions\MissingConfigurationException;

trait SettingsTrait
{
    public function overload(SettingsInterface $overloadSettings): static
    {
        $values = $overloadSettings->decodeValues();
        //$this->encodeValues($values);

        return $this;
    }

    public function is(string $code, string $subCode, bool|string|int $value = true): bool
    {
        $values = $this->decodeValues();
        if (!array_key_exists($code, $values)) {
            return false;
        }

        if (!array_key_exists($subCode, $values[$code])) {
            return false;
        }

        return $values[$code][$subCode] === $value;
    }

    public function get(string $code, string $subCode): array|bool|int|null|string
    {
        $values = $this->decodeValues();
        if (!array_key_exists($code, $values)) {
            return null;
        }

        if (!array_key_exists($subCode, $values[$code])) {
            return null;
        }

        return $values[$code][$subCode];
    }

    public function aget(string $code): ?array
    {
        $values = $this->decodeValues();

        if (!array_key_exists($code, $values)) {
            return null;
        }

        return $values[$code];
    }

    public function addValue(string $code, string $subCode, int|string $value): static
    {
        $values = $this->decodeValues();

        if (!array_key_exists($code, $values)) {
            $values[$code] = [];
        }

        if (!array_key_exists($subCode, $values[$code])) {
            $values[$code][$subCode] = [];
        }

        if(!is_array($values[$code][$subCode])){
            throw new MissingConfigurationException('Settings::addValue is only allowed on array');
        }

        if(!in_array($value , $values[$code][$subCode] )) {
            $values[$code][$subCode][] = $value;
        }

        $this->encodeValues($values);

        return $this;
    }

    public function setValue(string $code, string $subCode, array|bool|int|string $value): static
    {
        $values = $this->decodeValues();

        if (!array_key_exists($code, $values)) {
            $values[$code] = [];
        }
        $values[$code][$subCode] = $value;

        $this->encodeValues($values);

        return $this;
    }

    public function unsetValue(string $code, string $subCode): static
    {
        $values = $this->decodeValues();

        if (!array_key_exists($code, $values)) {

            return $this;
        }

        if (!array_key_exists($subCode, $values[$code])) {

            return $this;
        }

        unset($values[$code][$subCode]);

        $this->encodeValues($values);

        return $this;
    }
}