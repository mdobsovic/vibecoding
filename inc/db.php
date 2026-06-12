<?php
// Ochrana pred priamym pristupom k suboru
defined('APP') || exit;

/*
 * Pripojenie k databaze (MariaDB/MySQL) cez PDO.
 * Pripojenie je "lazy" - vytvori sa az pri prvom volani db() a potom sa znovu pouziva.
 * Pouzije sa az vo finalnej faze "Moje projekty" (ked bude use_db_projects = true).
 */

/**
 * Vrati zdielanu instanciu PDO pripojenia k databaze.
 */
// Strazne if kvoli tomu, ze tento subor sa nacitava cez 'require' z viacerych metod;
// bez neho by opakovane nacitanie v jednej poziadavke skoncilo chybou "Cannot redeclare db()".
if (!function_exists('db')) {
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    global $config;
    $db = $config['db'];

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $db['host'],
        $db['name'],
        $db['charset'] ?? 'utf8mb4'
    );

    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // chyby ako vynimky
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // vysledky ako asociativne pole
        PDO::ATTR_EMULATE_PREPARES   => false,                   // skutocne prepared statements
    ]);

    return $pdo;
}
}
