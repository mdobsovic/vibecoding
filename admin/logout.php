<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/admin_auth.php';

// Odhlasenie len cez POST + CSRF (ochrana pred odhlasenim cez podvrhnuty odkaz)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check((string) ($_POST['csrf'] ?? ''))) {
    admin_logout();
}

header('Location: ' . asset('admin/login.php'));
exit;
