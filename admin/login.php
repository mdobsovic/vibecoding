<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/admin_auth.php';

// Uz prihlaseny -> rovno do administracie
if (is_admin()) {
    header('Location: ' . asset('admin/'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = (string) ($_POST['csrf'] ?? '');
    $user = trim((string) ($_POST['user'] ?? ''));
    $pass = (string) ($_POST['pass'] ?? '');

    if (!csrf_check($csrf)) {
        $error = 'Platnosť formulára vypršala. Skúste to znova.';
    } elseif (admin_login($user, $pass)) {
        header('Location: ' . asset('admin/'));
        exit;
    } else {
        // Genericka hlaska - neprezradzujeme ci zlyhalo meno alebo heslo
        $error = 'Nesprávne prihlasovacie údaje.';
    }
}

$pageTitle   = 'Prihlásenie';
$adminLogged = false;
require __DIR__ . '/../inc/partials/admin-header.php';
?>

  <div class="admin-login">
    <h1 class="admin-login__title">Prihlásenie do administrácie</h1>

    <?php if ($error !== ''): ?>
    <div class="form-status form-status--error" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form class="admin-form" action="<?= e(asset('admin/login.php')) ?>" method="post" autocomplete="off">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <label class="form-field">
        <span class="form-field__label">Používateľské meno</span>
        <input type="text" name="user" required autofocus>
      </label>

      <label class="form-field">
        <span class="form-field__label">Heslo</span>
        <input type="password" name="pass" required>
      </label>

      <button type="submit" class="btn btn--primary btn--full">Prihlásiť sa</button>
    </form>
  </div>

<?php require __DIR__ . '/../inc/partials/admin-footer.php'; ?>
