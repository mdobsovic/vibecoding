<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/Projects.php';

// Detail projektu sa zobrazuje vzdy z databazy
$slug = isset($_GET['slug']) ? (string) $_GET['slug'] : '';
$p    = $slug !== '' ? Projects::findBySlug($slug) : null;

// Projekt neexistuje alebo nie je zverejneny -> 404
if ($p === null) {
    http_response_code(404);
    $pageTitle       = 'Projekt sa nenašiel — Michal Dobšovič';
    $pageDescription = 'Požadovaný projekt neexistuje alebo nie je zverejnený.';
    require __DIR__ . '/inc/partials/header.php';
    ?>
    <main>
      <section class="section">
        <div class="container">
          <div class="project-detail__notfound">
            <p class="project-detail__eyebrow">Chyba 404</p>
            <h1 class="section__title">Projekt sa nenašiel</h1>
            <p class="section__subtitle">Tento projekt neexistuje alebo nie je zverejnený.</p>
            <p><a class="btn btn--primary" href="<?= e(asset('#projekty')) ?>">← Späť na projekty</a></p>
          </div>
        </div>
      </section>
    </main>
    <?php
    require __DIR__ . '/inc/partials/footer.php';
    exit;
}

$tech            = Projects::techToArray((string) $p['tech']);
$pageTitle       = $p['nazov'] . ' — Michal Dobšovič';
$pageDescription = mb_substr((string) $p['popis'], 0, 160);

require __DIR__ . '/inc/partials/header.php';
?>

  <main>
    <article class="section project-detail">
      <div class="container project-detail__inner">

        <!-- Navigacia spat -->
        <p class="project-detail__back">
          <a href="<?= e(asset('#projekty')) ?>">← Späť na projekty</a>
        </p>

        <header class="project-detail__head">
          <?php if (!empty($p['tag'])): ?>
          <span class="project-card__tag"><?= e($p['tag']) ?></span>
          <?php endif; ?>

          <h1 class="project-detail__title"><?= e($p['nazov']) ?></h1>
          <p class="project-detail__lead"><?= e($p['popis']) ?></p>

          <?php if ($tech): ?>
          <div class="project-card__tech project-detail__tech">
            <?php foreach ($tech as $t): ?><span><?= e($t) ?></span><?php endforeach; ?>
          </div>
          <?php endif; ?>

          <?php if (!empty($p['url'])): ?>
          <p class="project-detail__actions">
            <a class="btn btn--primary" href="<?= e($p['url']) ?>" target="_blank" rel="noopener">
              Zobraziť naživo →
            </a>
          </p>
          <?php endif; ?>
        </header>

        <!-- Detailny popis. Obsah pise len prihlaseny admin (doverovany), preto sa vykresluje ako HTML. -->
        <?php if (!empty($p['popis_html'])): ?>
        <div class="project-detail__body">
          <?= $p['popis_html'] ?>
        </div>
        <?php endif; ?>

        <!-- Miesto pre galeriu obrazkov (pripravene na dalsi krok) -->

      </div>
    </article>
  </main>

<?php require __DIR__ . '/inc/partials/footer.php'; ?>
