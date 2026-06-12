<?php
/*
 * Pomocny nastroj na vygenerovanie hashu admin hesla pre inc/config.php.
 *
 * POUZITIE:
 *   1. Docasne nahraj tento subor na hosting (alebo spusti lokalne, ak mas PHP).
 *   2. Otvor ho v prehliadaci, zadaj heslo a skopiruj vygenerovany hash.
 *   3. Vloz hash do inc/config.php -> 'admin' => ['pass_hash' => '...'].
 *   4. !!! PO POUZITI TENTO SUBOR ZMAZ ZO SERVERA !!! (nepatri na produkciu)
 */

// Funguje aj cez prehliadac aj z CLI (php tools/make-hash.php heslo)
$hash = null;
$cli  = (PHP_SAPI === 'cli');

if ($cli && isset($argv[1])) {
    $hash = password_hash((string) $argv[1], PASSWORD_DEFAULT);
    echo $hash . PHP_EOL;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pass']) && $_POST['pass'] !== '') {
    $hash = password_hash((string) $_POST['pass'], PASSWORD_DEFAULT);
}
?>
<!DOCTYPE html>
<html lang="sk">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Generátor hashu hesla</title>
  <style>
    body { font-family: system-ui, sans-serif; max-width: 640px; margin: 40px auto; padding: 0 16px; }
    .warn { background: #fff4e5; border: 1px solid #ffb74d; padding: 12px 16px; border-radius: 8px; }
    input[type=text] { width: 100%; padding: 10px; font-size: 16px; }
    button { padding: 10px 18px; font-size: 16px; margin-top: 12px; cursor: pointer; }
    code { display: block; background: #0f172a; color: #e2e8f0; padding: 14px; border-radius: 8px;
           word-break: break-all; margin-top: 16px; font-family: monospace; }
  </style>
</head>
<body>
  <h1>Generátor hashu admin hesla</h1>
  <p class="warn"><strong>Pozor:</strong> tento súbor po použití <strong>zmažte zo servera</strong>.
     Nepatrí na produkciu.</p>

  <form method="post" autocomplete="off">
    <label>Heslo:<br><input type="text" name="pass" autofocus></label><br>
    <button type="submit">Vygenerovať hash</button>
  </form>

  <?php if ($hash !== null): ?>
  <p>Skopírujte tento hash do <code>inc/config.php</code> ako <code>'pass_hash'</code>:</p>
  <code><?= htmlspecialchars($hash, ENT_QUOTES, 'UTF-8') ?></code>
  <?php endif; ?>
</body>
</html>
