<?php

namespace Lib;

use Exception;
use PDO;
use PDOStatement;

class DB {

    private static ?PDO $pdo;

    private static function init(): void {
        if (!isset(self::$pdo)) {
            self::$pdo = new PDO(
                'mysql:host=' . \DB['host'] . ';port=' . \DB['port'] . ';dbname=' . \DB['name'] . ';',
                \DB['user'],
                \DB['pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }
    }

    public static function execQuery(string $query, array $params = [], bool $needToReconnect = false): PDOStatement
    {
        if ($needToReconnect) self::$pdo = null;
        self::init();
        $stmt = self::$pdo->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    public static function getQuery(string $query, array $params = [], bool $needToReconnect = false): array
    {
        $stmt = self::execQuery($query, $params, $needToReconnect);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getQueryWithKeys(string $query, string $keyColumn, array $params = [], bool $needToReconnect = false): array {
        $rows = self::getQuery($query, $params, $needToReconnect);
        return array_combine(array_column($rows, $keyColumn), $rows);
    }
}