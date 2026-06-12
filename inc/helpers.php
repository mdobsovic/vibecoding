<?php
// Ochrana pred priamym pristupom k suboru
defined('APP') || exit;

/*
 * Pomocne funkcie pouzivane napriec aplikaciou.
 */

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
