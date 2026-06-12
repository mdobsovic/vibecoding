<?php
// Ochrana pred priamym pristupom k suboru
defined('APP') || exit;

// Premenne nastavi vstupna stranka pred includom:
//   $pageTitle    - titulok zalozky
//   $adminEditor  - true ak sa ma nacitat WYSIWYG editor (Trix) na strane formulara
//   $adminLogged  - true ak je admin prihlaseny (zobrazi navigacne odkazy)
$pageTitle   = $pageTitle   ?? 'Administrácia';
$adminEditor = $adminEditor ?? false;
$adminLogged = $adminLogged ?? is_admin();
?>
<!DOCTYPE html>
<html lang="sk">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($pageTitle) ?> — Admin</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= e(asset_v('css/style.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset_v('css/admin.css')) ?>">
  <?php if ($adminEditor): ?>
  <link rel="stylesheet" href="<?= e(asset('vendor/trix/trix.css')) ?>">
  <?php endif; ?>
</head>
<body class="admin">

  <header class="admin-header">
    <div class="container admin-header__inner">
      <a href="<?= e(asset('admin/')) ?>" class="admin-header__logo">
        <span class="nav__logo-mark">MD</span>
        <span>Administrácia projektov</span>
      </a>

      <?php if ($adminLogged): ?>
      <nav class="admin-header__nav">
        <a href="<?= e(asset('admin/')) ?>">Projekty</a>
        <a href="<?= e(asset('')) ?>" target="_blank" rel="noopener">Zobraziť web ↗</a>
        <form action="<?= e(asset('admin/logout.php')) ?>" method="post" class="admin-header__logout">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <button type="submit" class="btn btn--ghost btn--sm">Odhlásiť sa</button>
        </form>
      </nav>
      <?php endif; ?>
    </div>
  </header>

  <main class="admin-main container">
