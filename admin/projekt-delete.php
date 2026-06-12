<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/admin_auth.php';
require __DIR__ . '/../inc/Projects.php';

require_admin();

// Len POST + CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check((string) ($_POST['csrf'] ?? ''))) {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        Projects::delete($id);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Projekt bol zmazaný.'];
    }
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akciu sa nepodarilo overiť.'];
}

header('Location: ' . asset('admin/'));
exit;
