<?php

namespace Architekt;

use Architekt\DB\DBConnexion;
use Architekt\Library\File;

class Transaction
{
    private static array $list = [];

    public static function start(string $dataBaseInstanceName = 'main'): bool
    {
        $name = uniqid();
        if (!count(self::$list[$dataBaseInstanceName] ?? [])) {
            self::$list[$dataBaseInstanceName][] = $name;
            return DBConnexion::get()->transactionStart() && File::transactionStart();
        }

        self::$list[$dataBaseInstanceName][] = $name;

        return true;

    }

    public static function commit(string $dataBaseInstanceName = 'main'): bool
    {
        $last = self::last($dataBaseInstanceName);

        if (!$last) return true;

        self::remove($dataBaseInstanceName, $last);

        if (!count(self::$list[$dataBaseInstanceName] ?? [])) {

            return DBConnexion::get()->transactionCommit() &&  File::transactionCommit();
        }

        return true;
    }

    public static function rollback(string $dataBaseInstanceName = 'main'): bool
    {
        $last = self::last($dataBaseInstanceName);

        if (!$last) return true;

        self::remove($dataBaseInstanceName, $last);

        if (!count(self::$list[$dataBaseInstanceName] ?? [])) {
            return DBConnexion::get()->transactionRollBack() && File::transactionRollback();
        }

        return true;
    }


    private static function last(string $dataBaseInstanceName = 'main'): ?string
    {
        if (!count(self::$list[$dataBaseInstanceName] ?? [])) {
            return null;
        }
        end(self::$list[$dataBaseInstanceName]);
        return current(self::$list[$dataBaseInstanceName]);
    }

    private static function remove(string $dataBaseInstanceName, string $name): void
    {
        if (!count(self::$list[$dataBaseInstanceName] ?? [])) {
            return;
        }

        $key = array_search($name, self::$list[$dataBaseInstanceName]);
        if ($key !== false) {
            unset(self::$list[$dataBaseInstanceName][$key]);
        }
    }

}