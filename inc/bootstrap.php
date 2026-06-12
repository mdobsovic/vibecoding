<?php
/*
 * Bootstrap - spolocny zaklad pre vsetky PHP vstupne body (index.php, api/...).
 * Definuje konstantu APP, nacita konfiguraciu, spusti session a pomocne funkcie.
 */

// Znacka, ze beh ide cez aplikaciu (vyuzivaju ju include subory na ochranu pred priamym pristupom)
define('APP', true);

// Nacitanie konfiguracie. Ak chyba config.php, jasna hlaska (nepretazovat navstevnika detailmi).
$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Chyba konfiguracie: chyba subor inc/config.php (vytvor ho podla inc/config.sample.php).');
}

/** @var array $config Globalna konfiguracia aplikacie */
$config = require $configFile;

// Bezpecne nastavenie session cookie a spustenie session (kvoli CSRF tokenu)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        // 'secure' => true,  // odporucane zapnut na hostingu cez HTTPS
    ]);
    session_start();
}

// Spravanie chyb: na produkcii nezobrazovat detaily navstevnikovi, ale logovat
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Pomocne funkcie
require __DIR__ . '/helpers.php';
