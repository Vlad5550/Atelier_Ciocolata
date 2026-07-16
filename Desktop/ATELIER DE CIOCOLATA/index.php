<?php
require_once __DIR__ . "/php/db.php";
require_once __DIR__ . "/php/helpers.php";

// Folosit pentru afisarea barei de utilizator in header.
$user = getCurrentUser($pdo);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🏠 Acasa</title>
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<header>
  <h1>🍫 Atelier Ciocolata</h1>

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
    <?php if ($user): ?>
      👤 <?= e($user["username"]) ?> | 💰 <?= number_format((float) $user["money"], 2) ?> RON
      <a class="button-link" href="logout.php">🚪 Logout</a>
    <?php else: ?>
      <a class="button-link" href="auth.php">🔐 Login / Register</a>
    <?php endif; ?>
  </div>
</header>

<main>
  <div class="card fade-in">
    <h2>🎓 Proiect de Licenta</h2>
    <p>Platforma web dinamica pentru magazin online de ciocolata artizanala.</p>
  </div>

  <div class="card fade-in">
    <h2>✨ Descriere</h2>
    <p>Sistem e-commerce cu autentificare, cos de cumparaturi, administrare produse si comenzi salvate in MySQL.</p>
  </div>

  <div class="card fade-in">
    <h2>🧩 Tehnologii</h2>
    <p>PHP, JavaScript, XML, CSS3, MySQL, XSL.</p>
  </div>
</main>

</body>
</html>
