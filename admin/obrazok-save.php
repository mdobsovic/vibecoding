<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/admin_auth.php';
require __DIR__ . '/../inc/ProjectImages.php';

require_admin();

$projektId = isset($_POST['projekt_id']) ? (int) $_POST['projekt_id'] : 0;
$formUrl   = asset('admin/projekt-form.php') . ($projektId > 0 ? '?id=' . $projektId : '');

// Len POST + platny CSRF token
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check((string) ($_POST['csrf'] ?? ''))) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akciu sa nepodarilo overiť. Skúste to znova.'];
    header('Location: ' . $formUrl);
    exit;
}

$id  = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$img = $id > 0 ? ProjectImages::find($id) : null;

// Obrazok musi existovat a patrit danemu projektu
if ($img === null || (int) $img['projekt_id'] !== $projektId) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Obrázok sa nenašiel.'];
    header('Location: ' . $formUrl);
    exit;
}

$alt     = trim((string) ($_POST['alt'] ?? ''));
$alt     = mb_substr($alt, 0, 200);
$poradie = (int) ($_POST['poradie'] ?? 0);

ProjectImages::updateMeta($id, $alt, $poradie);

$_SESSION['flash'] = ['type' => 'success', 'message' => 'Obrázok bol upravený.'];
header('Location: ' . $formUrl);
exit;
