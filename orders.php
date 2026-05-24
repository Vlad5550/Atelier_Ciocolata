<?php
require_once __DIR__ . "/php/db.php";
require_once __DIR__ . "/php/helpers.php";

// Istoricul comenzilor este disponibil doar utilizatorilor logati.
requireLogin();

$user = getCurrentUser($pdo);

// Se citesc doar comenzile utilizatorului curent.
$stmt = $pdo->prepare(
    "SELECT id, total, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC"
);
$stmt->execute([$user["id"]]);
$orders = $stmt->fetchAll();

$itemsByOrder = [];

if ($orders) {
    $orderIds = array_column($orders, "id");
    $placeholders = implode(",", array_fill(0, count($orderIds), "?"));

    // Produsele comandate sunt grupate dupa id-ul comenzii.
    $stmt = $pdo->prepare(
        "SELECT oi.order_id, oi.quantity, oi.price, p.name
         FROM order_items oi
         JOIN products p ON p.id = oi.product_id
         WHERE oi.order_id IN ($placeholders)
         ORDER BY oi.id ASC"
    );
    $stmt->execute($orderIds);

    foreach ($stmt->fetchAll() as $item) {
        $itemsByOrder[(int) $item["order_id"]][] = $item;
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<title>📦 Comenzi</title>
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<header>
  <h1>📦 Istoric Comenzi</h1>
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
  <?php if (!$orders): ?>
    <div class="card">
      <p>📭 Nu ai comenzi salvate.</p>
      <a class="button-link" href="shop.php">🛍️ Inapoi la magazin</a>
    </div>
  <?php endif; ?>

  <?php foreach ($orders as $index => $order): ?>
    <div class="card">
      <h2>📦 Comanda #<?= $index + 1 ?></h2>
      <p>📌 Status: <?= e($order["status"]) ?></p>
      <p>💰 Total: <?= number_format((float) $order["total"], 2) ?> RON</p>
      <p>🕒 Data: <?= e($order["created_at"]) ?></p>

      <?php foreach ($itemsByOrder[(int) $order["id"]] ?? [] as $item): ?>
        <p>
          <?= e($item["name"]) ?>:
          <?= (int) $item["quantity"] ?> x <?= number_format((float) $item["price"], 2) ?> RON
        </p>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</main>

</body>
</html>
