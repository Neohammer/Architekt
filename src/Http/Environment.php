<?php

namespace Architekt\Http;

use Architekt\Http\Exceptions\InvalidServerConfigurationException;

class Environment
{
    private static array $environments = [];
    private static ?string $aliasCurrent = null;
    private static ?string $currentEnvironment = null;

    public static function add(string $alias, array $serverNames): void
    {
        self::$environments[$alias] = $serverNames;
        self::$currentEnvironment = null;
    }

    public static function requireFile(string $path, string $extension = 'php'): void
    {
        $alias = self::get();

        require_once(sprintf(
            '%s.%s.%s',
            $path,
            self::get(),
            $extension
        ));
    }

    public static function get(): ?string
    {
        if (null === self::$currentEnvironment) {
            $nameToFind = self::serverName();

            foreach (self::$environments as $environment => $names) {
                if (in_array($nameToFind, $names, true)) {
                    self::$currentEnvironment = $environment;
                    break;
                }
            }
        }
        if (null === self::$currentEnvironment) {
            throw new InvalidServerConfigurationException();
        }

        return self::$currentEnvironment;
    }

    public static function url($name): string
    {
        return sprintf(
            '//%s/',
            self::$environments[self::$currentEnvironment][$name]
        );
    }

    public static function serverName(): string
    {
        if (!array_key_exists('SERVER_NAME', $_SERVER)) {
            throw new InvalidServerConfigurationException("SERVER_NAME does not exists, please set it on server configuration");
        }

        return $_SERVER['SERVER_NAME'];
    }

    public static function serverProtocol(): string
    {
        return $_SERVER['SERVER_PORT'] === '80' ? 'http':'https';
    }
}