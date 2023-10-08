<?php

namespace Architekt\Utility;

interface SettingsInterface
{
    public function overload(SettingsInterface $overloadSettings): static;

    public function is(string $code, string $subCode, bool|string|int $value = true): bool;

    public function aget(string $code): ?array;

    public function get(string $code, string $subCode): array|bool|int|null|string;

    public function setValue(string $code, string $subCode, array|bool|int|string $value): static;

    public function addValue(string $code, string $subCode, int|string $value): static;
}