<?php
// Ochrana pred priamym pristupom k suboru
defined('APP') || exit;

/*
 * Autentifikacia do admin rozhrania (/admin).
 * Jeden admin: meno + hash hesla su v configu ('admin' => ['user', 'pass_hash']).
 */

/**
 * Je aktualny navstevnik prihlaseny ako admin?
 */
function is_admin(): bool
{
    return !empty($_SESSION['admin']);
}

/**
 * Pokus o prihlasenie. Vrati true pri uspechu.
 * Pri neuspechu kratke zdrzanie (anti-brute-force) a pocitadlo pokusov v session.
 */
function admin_login(string $user, string $pass): bool
{
    global $config;

    $cfg  = $config['admin'] ?? [];
    $hash = (string) ($cfg['pass_hash'] ?? '');
    $name = (string) ($cfg['user'] ?? '');

    // Bez nastaveneho hashu sa neda prihlasit (ochrana pred prazdnou konfiguraciou)
    $ok = $hash !== ''
        && hash_equals($name, $user)
        && password_verify($pass, $hash);

    if (!$ok) {
        // Mierne zdrzanie a evidencia pokusov (sttazenie hadania hesla)
        $_SESSION['admin_attempts'] = (int) ($_SESSION['admin_attempts'] ?? 0) + 1;
        sleep(1);
        return false;
    }

    // Uspech: obnova session id (ochrana pred session fixation) a nastavenie priznaku
    session_regenerate_id(true);
    $_SESSION['admin'] = true;
    unset($_SESSION['admin_attempts']);

    return true;
}

/**
 * Vyzaduje prihlaseneho admina. Ak nie je, presmeruje na login a ukonci skript.
 */
function require_admin(): void
{
    if (!is_admin()) {
        header('Location: ' . asset('admin/login.php'));
        exit;
    }
}

/**
 * Odhlasenie - zrusi admin priznak v session.
 */
function admin_logout(): void
{
    unset($_SESSION['admin'], $_SESSION['admin_attempts']);
    session_regenerate_id(true);
}
