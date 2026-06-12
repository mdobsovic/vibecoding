<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/admin_auth.php';
require __DIR__ . '/../inc/Projects.php';
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

// Projekt musi existovat (obrazky sa daju nahravat len k ulozenemu projektu)
if ($projektId <= 0 || Projects::find($projektId) === null) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Projekt sa nenašiel.'];
    header('Location: ' . asset('admin/'));
    exit;
}

// Priprava priecinka galerie (ak este neexistuje)
$dir = gallery_dir();
if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Priečinok galérie sa nepodarilo vytvoriť.'];
    header('Location: ' . $formUrl);
    exit;
}

// Vstup z formulara: viacero suborov cez name="obrazky[]"
$files = $_FILES['obrazky'] ?? null;
if (!is_array($files) || !isset($files['name']) || !is_array($files['name'])) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Nevybrali ste žiadny obrázok.'];
    header('Location: ' . $formUrl);
    exit;
}

$pridane = 0;
$chyby   = [];

$count = count($files['name']);
for ($i = 0; $i < $count; $i++) {
    $err  = $files['error'][$i] ?? UPLOAD_ERR_NO_FILE;
    $name = (string) ($files['name'][$i] ?? '');

    // Prazdny slot (uzivatel nevybral subor v tomto poli) - preskocime ticho
    if ($err === UPLOAD_ERR_NO_FILE) {
        continue;
    }

    $popis = $name !== '' ? $name : 'súbor';

    if ($err !== UPLOAD_ERR_OK) {
        // Najcastejsie prekrocenie limitu servera (upload_max_filesize / post_max_size)
        $chyby[] = $popis . ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE ? ' (príliš veľký)' : '');
        continue;
    }

    $tmp  = (string) ($files['tmp_name'][$i] ?? '');
    $size = (int) ($files['size'][$i] ?? 0);

    if ($tmp === '' || !is_uploaded_file($tmp)) {
        $chyby[] = $popis;
        continue;
    }

    if ($size <= 0 || $size > ProjectImages::MAX_BYTES) {
        $chyby[] = $popis . ' (max. 2 MB)';
        continue;
    }

    // Overenie typu podla skutocneho obsahu (getimagesize zaroven overi, ze ide o obrazok),
    // nie podla pripony z prehliadaca. getimagesize je sucast jadra PHP (netreba fileinfo).
    $info = @getimagesize($tmp);
    $mime = is_array($info) && isset($info['mime']) ? (string) $info['mime'] : '';
    $ext  = ProjectImages::extForMime($mime);
    if ($ext === null) {
        $chyby[] = $popis . ' (nepodporovaný formát)';
        continue;
    }

    // Vlastny bezpecny nazov suboru - nepouzivame nazov od uzivatela
    $subor = 'projekt' . $projektId . '-' . bin2hex(random_bytes(8)) . '.' . $ext;
    $cesta = $dir . '/' . $subor;

    if (!move_uploaded_file($tmp, $cesta)) {
        $chyby[] = $popis;
        continue;
    }
    @chmod($cesta, 0644);

    ProjectImages::add($projektId, $subor);
    $pridane++;
}

// Vyhodnotenie a flash sprava
if ($pridane > 0 && !$chyby) {
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Nahraté obrázky: ' . $pridane . '.'];
} elseif ($pridane > 0) {
    $_SESSION['flash'] = [
        'type'    => 'success',
        'message' => 'Nahraté: ' . $pridane . '. Preskočené: ' . implode(', ', $chyby) . '.',
    ];
} else {
    $_SESSION['flash'] = [
        'type'    => 'error',
        'message' => 'Žiadny obrázok sa nepodarilo nahrať.' . ($chyby ? ' Problém: ' . implode(', ', $chyby) . '.' : ''),
    ];
}

header('Location: ' . $formUrl);
exit;
