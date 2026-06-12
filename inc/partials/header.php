<?php
// Ochrana pred priamym pristupom k suboru
defined('APP') || exit;

// Volitelne premenne stranky (nastavia ich jednotlive vstupne body pred includom hlavicky).
$pageTitle       = $pageTitle       ?? 'Michal Dobšovič — IT lektor a rozcestník projektov';
$pageDescription = $pageDescription ?? 'Osobna stranka a rozcestnik projektov Michala Dobsovica - lektora IT skoleni.';
?>
<!DOCTYPE html>
<html lang="sk">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= e($pageDescription) ?>">
  <title><?= e($pageTitle) ?></title>

  <!-- Pisma: Manrope (text) a JetBrains Mono (ukazky kodu) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body>

  <!-- Kotva uplne na vrchu stranky (mimo sticky hlavicky, aby skrolovanie hore fungovalo) -->
  <span id="top"></span>

  <!-- Navigacia. Odkazy su root-relativne (cez asset()), aby fungovali aj z podstranok. -->
  <header class="site-header">
    <nav class="nav container">
      <a href="<?= e(asset('')) ?>" class="nav__logo">
        <span class="nav__logo-mark">MD</span>
        <span class="nav__logo-text">Michal Dobšovič</span>
      </a>

      <button class="nav__toggle" id="navToggle" aria-label="Otvoriť menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>

      <ul class="nav__menu" id="navMenu">
        <li><a href="<?= e(asset('#o-mne')) ?>">O mne</a></li>
        <li><a href="<?= e(asset('#projekty')) ?>">Projekty</a></li>
        <li><a href="<?= e(asset('#kontakt')) ?>" class="nav__cta">Kontakt</a></li>
      </ul>
    </nav>
  </header>
