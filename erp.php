<?php
require_once __DIR__ . "/php/db.php";
require_once __DIR__ . "/php/helpers.php";

// Pagina ERP ofera linkuri catre exporturile XML.
requireLogin();

$user = getCurrentUser($pdo);
// Doar administratorii pot deschide exporturile XML.
$admin = isAdmin($pdo);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>📄 ERP XML</title>
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<header>
  <h1>📄 Export ERP</h1>
  <nav>
    <a href="index.php">🏠 Acasa</a>
    <a href="shop.php">🛍️ Magazin</a>
    <a href="cart.php">🛒 Cos</a>
    <a href="orders.php">📦 Comenzi</a>
    <?php if (isNitaAdmin($pdo)): ?>
      <a href="erp.php">📄 ERP/XML</a>
      <a href="admin.php">⚙️ Admin</a>
    <?php endif; ?>
  </nav>

  <div id="userBar">
    👤 <?= e($user["username"]) ?> | 💰 <?= number_format((float) $user["money"], 2) ?> RON
    <a class="button-link" href="logout.php">🚪 Logout</a>
  </div>
</header>

<main>
  <?php if (!$admin): ?>
    <p class="message error">
      🔒 Exportul ERP este disponibil doar pentru admin.
    </p>
    <div class="card">
      <p>🗄️ In phpMyAdmin ruleaza:</p>
      <code>UPDATE users SET role = 'admin' WHERE username = 'numele_tau';</code>
    </div>
  <?php else: ?>
    <div class="card wide-card">
      <h2>🍫 XML pentru produse</h2>
      <p>Catalogul de produse este exportat ca document XML si afisat cu XSL.</p>
      <a class="button-link" href="export_products_xml.php" target="_blank">📄 Deschide XML produse</a>
    </div>

    <div class="card wide-card">
      <h2>📦 XML pentru comenzi</h2>
      <p>Comenzile sunt exportate ca document XML pentru preluare intr-un sistem ERP.</p>
      <a class="button-link" href="export_orders_xml.php" target="_blank">📄 Deschide XML comenzi</a>
    </div>
  <?php endif; ?>
</main>

</body>
</html>
