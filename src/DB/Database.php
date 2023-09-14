<?php

namespace Architekt\DB;

use Architekt\DB\Engines\DBEngineInterface;
use Architekt\DB\Engines\Mysqli;
use Architekt\DB\Engines\PDO;
use Architekt\DB\Exceptions\InvalidParameterException;
use Architekt\DB\Exceptions\MissingConfigurationException;

class Database
{
    const MYSQLI = 'mysqli';
    const PDO = 'pdo';

    /**
     * @var DBEngineInterface[]
     */
    static array $instances = [];
    static private array $config = [];

    static public function clone($name, $newType, $newName): DBEngineInterface
    {
        self::$config[$newName] = self::$config[$name];
        self::$config[$newName]['type'] = $newType;

        return self::engine($newName);
    }

    static public function engine(string $name = 'main'): DBEngineInterface
    {
        if (!array_key_exists($name, self::$instances)) {
            if (!array_key_exists($name, self::$config)) {
                throw new MissingConfigurationException(sprintf(
                    'Database instance %s is unknown',
                    $name
                ));
            }
            self::$instances[$name] = (self::build(self::$config[$name]['type']))
                ->configure(
                    self::$config[$name]['hostname'],
                    self::$config[$name]['user'],
                    self::$config[$name]['password'],
                    self::$config[$name]['database'],
                    self::$config[$name]['port'],
                    self::$config[$name]['charset']
                );
        }

        return self::$instances[$name];
    }

    static public function configure(
        string  $name,
        string  $type,
        string  $hostname,
        string  $user,
        string  $password,
        string  $database,
        ?int    $port = null,
        ?string $charset = 'UTF8'
    ): void
    {
        self::$config[$name] = [
            'type' => $type,
            'hostname' => $hostname,
            'user' => $user,
            'password' => $password,
            'database' => $database,
            'port' => $port,
            'charset' => $charset,
        ];

    }

    /**
     * @throws InvalidParameterException
     */
    static private function build(string $type): DBEngineInterface
    {
        return match ($type) {
            self::MYSQLI => new Mysqli(),
            self::PDO => new PDO(),
            default => throw new InvalidParameterException('Database type : %s is unknown')
        };
    }
}