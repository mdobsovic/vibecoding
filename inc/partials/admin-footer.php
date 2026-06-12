<?php
// Ochrana pred priamym pristupom k suboru
defined('APP') || exit;

$adminEditor = $adminEditor ?? false;
?>
  </main>

  <footer class="admin-footer">
    <div class="container">
      <p>© <span id="year"></span> Michal Dobšovič — interná administrácia</p>
    </div>
  </footer>

  <script src="<?= e(asset('js/main.js')) ?>"></script>
  <?php if ($adminEditor): ?>
  <script type="text/javascript" src="<?= e(asset('vendor/trix/trix.umd.min.js')) ?>"></script>
  <?php endif; ?>
</body>
</html>
