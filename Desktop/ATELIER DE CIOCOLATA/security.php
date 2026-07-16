<?php
require_once __DIR__ . "/php/db.php";
require_once __DIR__ . "/php/helpers.php";

// Pagina explica masurile de securitate implementate in proiect.
requireLogin();

$user = getCurrentUser($pdo);
$admin = isAdmin($pdo);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🛡️ Securitate</title>
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<header>
  <h1>🛡️ Securitate</h1>
  <nav>
    <a href="index.php">🏠 Acasa</a>
    <a href="shop.php">🛍️ Magazin</a>
    <a href="cart.php">🛒 Cos</a>
    <a href="orders.php">📦 Comenzi</a>
    <?php if (isNitaAdmin($pdo)): ?>
      <a href="erp.php">📄 ERP/XML</a>
      <a href="admin.php">⚙️ Admin</a>
    <?php endif; ?>
    <a href="security.php">🛡️ Securitate</a>
  </nav>

  <div id="userBar">
    👤 <?= e($user["username"]) ?> | 💰 <?= number_format((float) $user["money"], 2) ?> RON
    <a class="button-link" href="logout.php">🚪 Logout</a>
  </div>
</header>

<main>
  <div class="card wide-card">
    <h2>🛡️ Metode implementate</h2>
    <p>Parolele sunt salvate cu <code>password_hash()</code> si verificate cu <code>password_verify()</code>.</p>
    <p>Interogarile cu date de la utilizator folosesc PDO prepared statements.</p>
    <p>Afisarea datelor foloseste <code>htmlspecialchars()</code> prin functia <code>e()</code>.</p>
    <p>Autentificarea este bazata pe sesiuni PHP, iar paginile protejate folosesc <code>requireLogin()</code>.</p>
    <p>Administrarea si exportul ERP/XML sunt limitate prin rolul <code>admin</code>.</p>
    <p>Finalizarea comenzii foloseste tranzactie SQL pentru salvarea comenzii si scaderea stocului.</p>
  </div>

  <div class="card wide-card">
    <h2>🧪 Programe folosite pentru testare</h2>
    <p>OWASP ZAP: scanare automata pentru vulnerabilitati web.</p>
    <p>Burp Suite Community: verificare manuala a cererilor HTTP, formularelor si sesiunilor.</p>
    <p>php -l: verificarea sintaxei fisierelor PHP.</p>
  </div>

  <?php if (!$admin): ?>
    <p class="message error">
      Pentru testarea completa a exporturilor XML este necesar rolul de admin.
    </p>
  <?php endif; ?>
</main>

</body>
</html>
