<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/Projects.php';

$projekty = Projects::all();

// Flash sprava a povodne hodnoty po odoslani bez JavaScriptu (fallback z api/kontakt.php)
$flash = $_SESSION['flash'] ?? null;
$old   = $_SESSION['old'] ?? [];
unset($_SESSION['flash'], $_SESSION['old']);

require __DIR__ . '/inc/partials/header.php';
?>

  <main>

    <!-- Hero / uvod -->
    <section class="hero">
      <div class="container hero__inner">
        <div class="hero__content">
          <p class="hero__eyebrow">IT lektor · IT LEARNING SLOVAKIA</p>
          <h1 class="hero__title">
            Ahoj, som Michal Dobšovič
          </h1>
          <p class="hero__lead">
            Venujem sa školeniam v oblasti serverov, počítačových sietí,
            vývoja webových aplikácií a databáz. Táto stránka je rozcestníkom
            mojich projektov.
          </p>
          <div class="hero__actions">
            <a href="#projekty" class="btn btn--primary">Pozrieť projekty</a>
            <a href="#kontakt" class="btn btn--ghost">Napísať mi</a>
          </div>
        </div>

        <!-- Fotografia autora -->
        <div class="hero__photo">
          <div class="hero__photo-frame">
            <img src="img/michal-dobsovic.jpg"
                 alt="Michal Dobšovič — IT lektor"
                 width="1000" height="1000"
                 loading="eager">
          </div>
        </div>
      </div>
    </section>

    <!-- O mne -->
    <section class="section" id="o-mne">
      <div class="container">
        <header class="section__head">
          <h2 class="section__title">O mne</h2>
          <p class="section__subtitle">Čomu sa venujem a v čom školím.</p>
        </header>

        <div class="about">
          <div class="about__text">
            <p>
              Som lektor v <a href="https://www.itlearning.sk" target="_blank" rel="noopener">IT LEARNING SLOVAKIA</a>.
              Zameriavam sa predovšetkým na praktické školenia, kde sa snažím
              odovzdávať skúsenosti zrozumiteľne a s dôrazom na reálne použitie.
            </p>
            <p>
              Ak hľadáš školenie alebo spoluprácu v niektorej z týchto oblastí,
              neváhaj ma kontaktovať.
            </p>
          </div>

          <ul class="skills">
            <li class="skill"><span class="skill__dot"></span>Windows Server</li>
            <li class="skill"><span class="skill__dot"></span>Linuxové servery</li>
            <li class="skill"><span class="skill__dot"></span>Počítačové siete — najmä MikroTik</li>
            <li class="skill"><span class="skill__dot"></span>Vývoj web aplikácií (HTML, JS, CSS, PHP)</li>
            <li class="skill"><span class="skill__dot"></span>Databázy — MS SQL Server a MySQL / MariaDB</li>
          </ul>
        </div>
      </div>
    </section>

    <!-- Projekty -->
    <section class="section section--alt" id="projekty">
      <div class="container">
        <header class="section__head">
          <h2 class="section__title">Moje projekty</h2>
          <p class="section__subtitle">Výber projektov, ktorý budem postupne dopĺňať.</p>
        </header>

        <!-- Karty sa generuju z Projects::all() (zatial staticke pole, neskor z databazy) -->
        <div class="projects">
          <?php foreach ($projekty as $p): ?>
          <article class="project-card">
            <?php if (!empty($p['tag'])): ?>
            <div class="project-card__top">
              <span class="project-card__tag"><?= e($p['tag']) ?></span>
            </div>
            <?php endif; ?>
            <h3 class="project-card__title"><?= e($p['nazov']) ?></h3>
            <p class="project-card__desc"><?= e($p['popis']) ?></p>
            <?php if (!empty($p['tech'])): ?>
            <div class="project-card__tech">
              <?php foreach ($p['tech'] as $t): ?><span><?= e($t) ?></span><?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($p['slug'])): ?>
            <a class="project-card__link" href="<?= e(asset('projekt/' . $p['slug'])) ?>">Zobraziť projekt →</a>
            <?php elseif (!empty($p['url'])): ?>
            <a class="project-card__link" href="<?= e($p['url']) ?>" target="_blank" rel="noopener">Zobraziť projekt →</a>
            <?php endif; ?>
          </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Kontakt -->
    <section class="section" id="kontakt">
      <div class="container">
        <header class="section__head">
          <h2 class="section__title">Kontakt</h2>
          <p class="section__subtitle">Napíš mi a ozvem sa ti.</p>
        </header>

        <div class="contact">
          <!-- Kontaktny formular. Odosielanie cez fetch (js/main.js), s fallbackom na klasicky POST. -->
          <form class="contact-form" id="contactForm" action="api/kontakt.php" method="post" novalidate>

            <?php if ($flash): ?>
            <div class="form-status form-status--<?= e($flash['type']) ?>" role="alert">
              <?= e($flash['message']) ?>
            </div>
            <?php endif; ?>

            <!-- CSRF token proti podvrhnutiu formulara -->
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <!-- Casova znacka pre anti-spam (prilis rychle odoslanie = bot) -->
            <input type="hidden" name="ts" value="<?= time() ?>">

            <!-- Honeypot - skryte pole, ktore ludia nevidia; ak ho bot vyplni, spravu odmietneme -->
            <div class="hp-field" aria-hidden="true">
              <label>Webová stránka <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <div class="form-row">
              <label class="form-field">
                <span class="form-field__label">Meno</span>
                <input type="text" name="meno" placeholder="Tvoje meno" required
                       value="<?= e($old['meno'] ?? '') ?>">
              </label>

              <label class="form-field">
                <span class="form-field__label">E-mail</span>
                <input type="email" name="email" placeholder="tvoj@email.sk" required
                       value="<?= e($old['email'] ?? '') ?>">
              </label>
            </div>

            <label class="form-field">
              <span class="form-field__label">Predmet</span>
              <input type="text" name="predmet" placeholder="O čo ide?"
                     value="<?= e($old['predmet'] ?? '') ?>">
            </label>

            <label class="form-field">
              <span class="form-field__label">Správa</span>
              <textarea name="sprava" rows="5" placeholder="Tvoja správa..." required><?= e($old['sprava'] ?? '') ?></textarea>
            </label>

            <button type="submit" class="btn btn--primary btn--full">Odoslať správu</button>
          </form>

          <aside class="contact-info">
            <h3 class="contact-info__title">Ďalšie možnosti</h3>
            <ul class="contact-info__list">
              <li>
                <span class="contact-info__label">Spoločnosť</span>
                <a href="https://www.itlearning.sk" target="_blank" rel="noopener">IT LEARNING SLOVAKIA</a>
              </li>
              <li>
                <span class="contact-info__label">Zameranie</span>
                <span>Servery · Siete · Web · Databázy</span>
              </li>
            </ul>
          </aside>
        </div>
      </div>
    </section>

  </main>

<?php require __DIR__ . '/inc/partials/footer.php'; ?>
