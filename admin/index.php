<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/admin_auth.php';
require __DIR__ . '/../inc/Projects.php';

require_admin();

$projekty = Projects::allForAdmin();

// Flash sprava (po ulozeni / zmazani)
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pageTitle = 'Projekty';
require __DIR__ . '/../inc/partials/admin-header.php';
?>

  <div class="admin-toolbar">
    <h1 class="admin-title">Moje projekty</h1>
    <a class="btn btn--primary" href="<?= e(asset('admin/projekt-form.php')) ?>">+ Pridať projekt</a>
  </div>

  <?php if ($flash): ?>
  <div class="form-status form-status--<?= e($flash['type']) ?>" role="alert"><?= e($flash['message']) ?></div>
  <?php endif; ?>

  <?php if (!$projekty): ?>
  <p class="admin-empty">Zatiaľ tu nie sú žiadne projekty. Pridajte prvý cez tlačidlo vyššie.</p>
  <?php else: ?>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Poradie</th>
          <th>Názov</th>
          <th>Technológie</th>
          <th>Stav</th>
          <th class="admin-table__actions">Akcie</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($projekty as $p): ?>
        <tr>
          <td><?= (int) $p['poradie'] ?></td>
          <td>
            <strong><?= e($p['nazov']) ?></strong>
            <span class="admin-table__slug">/projekt/<?= e($p['slug']) ?></span>
          </td>
          <td><?= e($p['tech']) ?></td>
          <td>
            <?php if (!empty($p['je_zverejneny'])): ?>
            <span class="badge badge--on">Zverejnený</span>
            <?php else: ?>
            <span class="badge badge--off">Skrytý</span>
            <?php endif; ?>
          </td>
          <td class="admin-table__actions">
            <a class="btn btn--ghost btn--sm" href="<?= e(asset('admin/projekt-form.php?id=' . (int) $p['id'])) ?>">Upraviť</a>
            <form action="<?= e(asset('admin/projekt-delete.php')) ?>" method="post"
                  class="admin-inline-form"
                  onsubmit="return confirm('Naozaj zmazať tento projekt? Túto akciu nie je možné vrátiť.');">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
              <button type="submit" class="btn btn--danger btn--sm">Zmazať</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

<?php require __DIR__ . '/../inc/partials/admin-footer.php'; ?>
