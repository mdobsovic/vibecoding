<?php
// Ochrana pred priamym pristupom k suboru
defined('APP') || exit;

/*
 * Pomocne funkcie pouzivane napriec aplikaciou.
 */

/*
 * Verzia statickych assetov (CSS/JS) pre cache-busting.
 * DOLEZITE: pri KAZDEJ zmene css/js suborov toto cislo ZVYS (napr. '1.0.1'),
 * aby prehliadace nacitali novu verziu a nepouzili stary subor z cache.
 */
defined('ASSET_VERSION') || define('ASSET_VERSION', '1.0.2');

/**
 * Escape pre bezpecny vystup do HTML (ochrana proti XSS).
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Vrati CSRF token pre aktualnu session (ak neexistuje, vytvori ho).
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Overi CSRF token z odoslaneho formulara. Pouziva porovnanie odolne voci timing utoku.
 */
function csrf_check(?string $token): bool
{
    return !empty($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Odosle JSON odpoved a ukonci skript.
 */
function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Zisti, ci ide o AJAX (fetch) poziadavku - podla hlavicky X-Requested-With.
 */
function is_ajax(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Zostavi URL k assetu / internemu odkazu s ohladom na base_url z konfiguracie.
 * Vdaka tomu cesty funguju aj na podstrankach (/projekt/...) a v adminovi (/admin/...).
 * Priklad: asset('css/style.css') -> '/css/style.css'
 */
function asset(string $path): string
{
    global $config;
    $base = isset($config['base_url']) && $config['base_url'] !== '' ? $config['base_url'] : '/';
    // Kotvy a absolutne URL nechame tak (napr. '#kontakt' nizsie riesi base osobitne)
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/**
 * Absolutna cesta k priecinku s nahratymi obrazkami galerie (na disku servera).
 * helpers.php je v inc/, takze koren projektu je o uroven vyssie.
 */
function gallery_dir(): string
{
    return dirname(__DIR__) . '/galeria';
}

/**
 * URL adresa obrazka galerie pre vystup do HTML (respektuje base_url).
 * Priklad: gallery_url('projekt5-abcd.jpg') -> '/galeria/projekt5-abcd.jpg'
 */
function gallery_url(string $file): string
{
    return asset('galeria/' . $file);
}

/**
 * Ako asset(), ale prida verziu pre cache-busting (?v=ASSET_VERSION).
 * Pouzivaj pre vlastne CSS/JS subory, ktore sa menia (style.css, admin.css, main.js).
 * Priklad: asset_v('css/style.css') -> '/css/style.css?v=1.0.0'
 */
function asset_v(string $path): string
{
    return asset($path) . '?v=' . ASSET_VERSION;
}

/**
 * Prevedie text (nazov projektu) na URL slug.
 * Slovenska diakritika sa prevadza cez rucnu mapu (iconv//TRANSLIT je na hostingu nespolahlivy).
 */
function slugify(string $text): string
{
    $map = [
        'á' => 'a', 'ä' => 'a', 'č' => 'c', 'ď' => 'd', 'é' => 'e', 'ě' => 'e',
        'í' => 'i', 'ĺ' => 'l', 'ľ' => 'l', 'ň' => 'n', 'ó' => 'o', 'ô' => 'o',
        'ŕ' => 'r', 'š' => 's', 'ť' => 't', 'ú' => 'u', 'ů' => 'u', 'ý' => 'y',
        'ž' => 'z', 'ł' => 'l', 'ż' => 'z', 'ź' => 'z', 'ć' => 'c', 'ę' => 'e',
        'ą' => 'a', 'ö' => 'o', 'ü' => 'u', 'ß' => 'ss',
    ];

    // Najprv na male pismena (mb kvoli diakritike), potom prevod podla mapy
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, $map);

    // Vsetko ostatne nealfanumericke nahradime pomlckou
    $text = preg_replace('~[^a-z0-9]+~', '-', $text);
    $text = trim((string) $text, '-');

    return $text !== '' ? $text : 'projekt';
}
