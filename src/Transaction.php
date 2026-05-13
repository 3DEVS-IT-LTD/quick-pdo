<?php

namespace ThreeDevs\QuickPdo;

use PDO;
use ThreeDevs\CallReturn\CallReturn;

class Transaction
{
    private static int $counter = 1;
    private static bool $started = false;
    private static ?PDO $pdo = null;

    private function __construct() {}
    private function __clone() {}

    public static function start(PDO $db): int
    {
        if (!self::$started) {
            self::$pdo = $db;
            self::$pdo->beginTransaction();

            self::$started = true;
            self::$counter = 1;

            return 1;
        }

        return ++self::$counter;
    }

    public static function commit(int $id): void
    {
        if ($id === 1 && self::$started && self::$pdo) {
            self::$pdo->commit();
            self::reset();
        }
    }

    public static function rollback(int $id): void
    {
        if ($id === 1 && self::$started && self::$pdo) {
            self::$pdo->rollBack();
            self::reset();
        }
    }

    public static function close(int $id, CallReturn $ret): void
    {
        if ($ret->is_error()) {
            self::rollback($id);
        } else {
            self::commit($id);
        }
    }

    private static function reset(): void
    {
        self::$counter = 1;
        self::$started = false;
        self::$pdo = null;
    }

    public static function inTransaction(): bool
    {
        return self::$started;
    }

    public static function depth(): int
    {
        return self::$counter;
    }
}