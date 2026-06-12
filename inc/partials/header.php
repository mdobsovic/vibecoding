<?php
// Ochrana pred priamym pristupom k suboru
defined('APP') || exit;
?>
<!DOCTYPE html>
<html lang="sk">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Osobna stranka a rozcestnik projektov Michala Dobsovica - lektora IT skoleni.">
  <title>Michal Dobšovič — IT lektor a rozcestník projektov</title>

  <!-- Pisma: Manrope (text) a JetBrains Mono (ukazky kodu) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <!-- Kotva uplne na vrchu stranky (mimo sticky hlavicky, aby skrolovanie hore fungovalo) -->
  <span id="top"></span>

  <!-- Navigacia -->
  <header class="site-header">
    <nav class="nav container">
      <a href="#top" class="nav__logo">
        <span class="nav__logo-mark">MD</span>
        <span class="nav__logo-text">Michal Dobšovič</span>
      </a>

      <button class="nav__toggle" id="navToggle" aria-label="Otvoriť menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>

      <ul class="nav__menu" id="navMenu">
        <li><a href="#o-mne">O mne</a></li>
        <li><a href="#projekty">Projekty</a></li>
        <li><a href="#kontakt" class="nav__cta">Kontakt</a></li>
      </ul>
    </nav>
  </header>
