<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/admin_auth.php';
require __DIR__ . '/../inc/Projects.php';

require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Editacia existujuceho projektu
$existing = null;
if ($id > 0) {
    $existing = Projects::find($id);
    if ($existing === null) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Projekt sa nenašiel.'];
        header('Location: ' . asset('admin/'));
        exit;
    }
}

// Hodnoty po neuspesnej validacii (z projekt-save.php) maju prednost pred DB / default
$old    = $_SESSION['form_old'] ?? null;
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_old'], $_SESSION['form_errors']);

// Zdroj predvyplnenych hodnot: stare (po chybe) -> existujuci zaznam -> prazdne defaulty
$src = $old ?? $existing ?? [];
$v = static function (string $key, $default = '') use ($src) {
    return $src[$key] ?? $default;
};

$isEdit    = $id > 0;
$pageTitle = $isEdit ? 'Upraviť projekt' : 'Nový projekt';

$adminEditor = true;
require __DIR__ . '/../inc/partials/admin-header.php';
?>

  <div class="admin-toolbar">
    <h1 class="admin-title"><?= $isEdit ? 'Upraviť projekt' : 'Nový projekt' ?></h1>
    <a class="btn btn--ghost" href="<?= e(asset('admin/')) ?>">← Späť na zoznam</a>
  </div>

  <?php if ($errors): ?>
  <div class="form-status form-status--error" role="alert">Skontrolujte prosím vyznačené polia.</div>
  <?php endif; ?>

  <form class="admin-form admin-form--wide" action="<?= e(asset('admin/projekt-save.php')) ?>" method="post">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= (int) $id ?>">
    <?php endif; ?>

    <label class="form-field <?= isset($errors['nazov']) ? 'form-field--error' : '' ?>">
      <span class="form-field__label">Názov projektu *</span>
      <input type="text" name="nazov" id="nazov" maxlength="150" required value="<?= e($v('nazov')) ?>">
      <?php if (isset($errors['nazov'])): ?><span class="form-field__error"><?= e($errors['nazov']) ?></span><?php endif; ?>
    </label>

    <label class="form-field <?= isset($errors['slug']) ? 'form-field--error' : '' ?>">
      <span class="form-field__label">Slug (URL) — nechajte prázdne pre automatický</span>
      <input type="text" name="slug" id="slug" maxlength="160" value="<?= e($v('slug')) ?>"
             placeholder="napr. moj-projekt">
      <small class="form-field__hint">Adresa projektu bude <code>/projekt/&lt;slug&gt;</code>. Povolené: malé písmená, čísla a pomlčky.</small>
      <?php if (isset($errors['slug'])): ?><span class="form-field__error"><?= e($errors['slug']) ?></span><?php endif; ?>
    </label>

    <label class="form-field <?= isset($errors['popis']) ? 'form-field--error' : '' ?>">
      <span class="form-field__label">Krátky popis * (zobrazí sa na karte a v zozname)</span>
      <textarea name="popis" rows="2" maxlength="500" required><?= e($v('popis')) ?></textarea>
      <?php if (isset($errors['popis'])): ?><span class="form-field__error"><?= e($errors['popis']) ?></span><?php endif; ?>
    </label>

    <div class="form-field">
      <span class="form-field__label">Detailný popis (zobrazí sa na podstránke projektu)</span>
      <!-- WYSIWYG editor Trix. Obsah sa uklada do skryteho inputu popis_html. -->
      <input type="hidden" id="popis_html_input" name="popis_html" value="<?= e($v('popis_html')) ?>">
      <trix-editor input="popis_html_input" class="trix-content"></trix-editor>
    </div>

    <div class="form-row">
      <label class="form-field">
        <span class="form-field__label">Technológie (oddelené čiarkami)</span>
        <input type="text" name="tech" maxlength="255" value="<?= e($v('tech')) ?>" placeholder="PHP, MariaDB, JavaScript">
      </label>

      <label class="form-field">
        <span class="form-field__label">Badge / štítok (voliteľné)</span>
        <input type="text" name="tag" maxlength="50" value="<?= e($v('tag')) ?>" placeholder="napr. Nové">
      </label>
    </div>

    <div class="form-row">
      <label class="form-field <?= isset($errors['url']) ? 'form-field--error' : '' ?>">
        <span class="form-field__label">Externý odkaz (voliteľné)</span>
        <input type="url" name="url" maxlength="255" value="<?= e($v('url')) ?>" placeholder="https://...">
        <?php if (isset($errors['url'])): ?><span class="form-field__error"><?= e($errors['url']) ?></span><?php endif; ?>
      </label>

      <label class="form-field form-field--narrow">
        <span class="form-field__label">Poradie</span>
        <input type="number" name="poradie" value="<?= e((string) $v('poradie', '0')) ?>" step="1">
      </label>
    </div>

    <label class="form-check">
      <input type="checkbox" name="je_zverejneny" value="1" <?= !empty($v('je_zverejneny', '1')) ? 'checked' : '' ?>>
      <span>Zverejniť projekt na webe</span>
    </label>

    <div class="admin-form__actions">
      <button type="submit" class="btn btn--primary">Uložiť projekt</button>
      <a class="btn btn--ghost" href="<?= e(asset('admin/')) ?>">Zrušiť</a>
    </div>
  </form>

<?php require __DIR__ . '/../inc/partials/admin-footer.php'; ?>
