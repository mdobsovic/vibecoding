<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/admin_auth.php';
require __DIR__ . '/../inc/Projects.php';

require_admin();

// Len POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . asset('admin/'));
    exit;
}

$id          = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$formUrl     = asset('admin/projekt-form.php') . ($id > 0 ? '?id=' . $id : '');

// CSRF
if (!csrf_check((string) ($_POST['csrf'] ?? ''))) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Platnosť formulára vypršala. Skúste to znova.'];
    header('Location: ' . $formUrl);
    exit;
}

// Nacitanie a ocistenie vstupov
$nazov      = trim((string) ($_POST['nazov'] ?? ''));
$slugInput  = trim((string) ($_POST['slug'] ?? ''));
$popis      = trim((string) ($_POST['popis'] ?? ''));
$popisHtml  = trim((string) ($_POST['popis_html'] ?? ''));
$tech       = trim((string) ($_POST['tech'] ?? ''));
$tag        = trim((string) ($_POST['tag'] ?? ''));
$url        = trim((string) ($_POST['url'] ?? ''));
$poradie    = (int) ($_POST['poradie'] ?? 0);
$zverejneny = !empty($_POST['je_zverejneny']) ? 1 : 0;

// Validacia
$errors = [];

$dlzkaNazov = mb_strlen($nazov);
if ($dlzkaNazov < 2 || $dlzkaNazov > 150) {
    $errors['nazov'] = 'Zadajte názov (2 až 150 znakov).';
}

$dlzkaPopis = mb_strlen($popis);
if ($dlzkaPopis < 5 || $dlzkaPopis > 500) {
    $errors['popis'] = 'Zadajte krátky popis (5 až 500 znakov).';
}

if ($url !== '' && (!filter_var($url, FILTER_VALIDATE_URL) || mb_strlen($url) > 255)) {
    $errors['url'] = 'Zadajte platnú URL adresu (vrátane https://).';
}

// Slug: ak je prazdny, vygeneruj z nazvu; inak normalizuj zadany
$slug = slugify($slugInput !== '' ? $slugInput : $nazov);
if ($slug === '' || mb_strlen($slug) > 160) {
    $errors['slug'] = 'Slug sa nepodarilo vytvoriť. Zadajte ho ručne (malé písmená, čísla, pomlčky).';
}

// Pri chybe vrat hodnoty spat do formulara
if ($errors) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_old'] = [
        'nazov'         => $nazov,
        'slug'          => $slugInput,
        'popis'         => $popis,
        'popis_html'    => $popisHtml,
        'tech'          => $tech,
        'tag'           => $tag,
        'url'           => $url,
        'poradie'       => $poradie,
        'je_zverejneny' => $zverejneny,
    ];
    header('Location: ' . $formUrl);
    exit;
}

// Zabezpecenie unikatnosti slugu (pri kolizii doplnime -2, -3, ...)
$base = $slug;
$i = 2;
while (Projects::slugExists($slug, $id)) {
    $slug = $base . '-' . $i;
    $i++;
}

$data = [
    'nazov'         => $nazov,
    'slug'          => $slug,
    'popis'         => $popis,
    'popis_html'    => $popisHtml,
    'tech'          => $tech,
    'tag'           => $tag,
    'url'           => $url,
    'poradie'       => $poradie,
    'je_zverejneny' => $zverejneny,
];

if ($id > 0) {
    Projects::update($id, $data);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Projekt bol upravený.'];
} else {
    Projects::create($data);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Projekt bol pridaný.'];
}

header('Location: ' . asset('admin/'));
exit;
