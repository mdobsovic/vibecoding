<?php
/*
 * Spracovanie kontaktneho formulara.
 * Podporuje AJAX (vracia JSON) aj klasicky POST bez JS (presmeruje spat s flash spravou).
 */

require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/mailer.php';

// Kam sa vracia navstevnik pri fallbacku bez JS
$backUrl = '../index.php#kontakt';

/**
 * Jednotna odpoved: pri AJAX vrati JSON, inak nastavi flash a presmeruje spat.
 *
 * @param array<string,string> $errors Chyby podla poli (prazdne = uspech)
 */
function respond(bool $ok, string $message, array $errors, int $status, string $backUrl, array $old = [], array $extra = []): void
{
    if (is_ajax()) {
        json_response(array_merge(['ok' => $ok, 'message' => $message, 'errors' => $errors], $extra), $status);
    }

    // Fallback bez JS - cez session prenesieme spravu a povodne hodnoty
    $_SESSION['flash'] = ['type' => $ok ? 'success' : 'error', 'message' => $message];
    if (!$ok) {
        $_SESSION['old'] = $old;
    }
    header('Location: ' . $backUrl);
    exit;
}

// Povolena je len metoda POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Neplatná požiadavka.', [], 405, $backUrl);
}

// --- Nacitanie a ocistenie vstupov ---
$meno    = trim((string) ($_POST['meno'] ?? ''));
$email   = trim((string) ($_POST['email'] ?? ''));
$predmet = trim((string) ($_POST['predmet'] ?? ''));
$sprava  = trim((string) ($_POST['sprava'] ?? ''));
$website = trim((string) ($_POST['website'] ?? '')); // honeypot
$ts      = (int) ($_POST['ts'] ?? 0);
$csrf    = (string) ($_POST['csrf'] ?? '');

$old = ['meno' => $meno, 'email' => $email, 'predmet' => $predmet, 'sprava' => $sprava];

// --- Bezpecnostne kontroly ---

// CSRF token
if (!csrf_check($csrf)) {
    respond(false, 'Platnosť formulára vypršala. Obnovte stránku a skúste znova.', [], 419, $backUrl, $old);
}

// Honeypot - skryte pole vyplni iba bot
if ($website !== '') {
    respond(false, 'Správu sa nepodarilo odoslať.', [], 400, $backUrl, $old);
}

// Casova pasca - odoslanie skor ako za 3 sekundy je takmer iste bot
if ($ts <= 0 || (time() - $ts) < 3) {
    respond(false, 'Formulár bol odoslaný príliš rýchlo. Skúste to prosím znova.', [], 400, $backUrl, $old);
}

// --- Validacia poli ---
$errors = [];

$dlzkaMeno = mb_strlen($meno);
if ($dlzkaMeno < 2 || $dlzkaMeno > 100) {
    $errors['meno'] = 'Zadajte meno (2 až 100 znakov).';
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
    $errors['email'] = 'Zadajte platnú e-mailovú adresu.';
}

if (mb_strlen($predmet) > 150) {
    $errors['predmet'] = 'Predmet je príliš dlhý (max. 150 znakov).';
}

$dlzkaSprava = mb_strlen($sprava);
if ($dlzkaSprava < 5 || $dlzkaSprava > 5000) {
    $errors['sprava'] = 'Napíšte správu (5 až 5000 znakov).';
}

if ($errors) {
    respond(false, 'Skontrolujte prosím vyplnené polia.', $errors, 422, $backUrl, $old);
}

// Odstranenie znakov noveho riadku z poli, ktore idu do hlaviciek e-mailu (anti-injection)
$meno  = str_replace(["\r", "\n"], ' ', $meno);
$email = str_replace(["\r", "\n"], '', $email);

// --- Odoslanie ---
try {
    send_contact_mail([
        'meno'    => $meno,
        'email'   => $email,
        'predmet' => $predmet,
        'sprava'  => $sprava,
    ]);
} catch (Throwable $e) {
    respond(false, 'Správu sa momentálne nepodarilo odoslať. Skúste to prosím neskôr.', [], 500, $backUrl, $old);
}

// Uspech - obnovime CSRF token, aby sa formular nedal odoslat opakovane refreshom.
// Novy token posleme do odpovede, aby ho JS mohol doplnit do formulara.
unset($_SESSION['csrf_token']);
respond(true, 'Ďakujem, správa bola odoslaná. Čoskoro sa ozvem.', [], 200, $backUrl, [], ['csrf' => csrf_token()]);
