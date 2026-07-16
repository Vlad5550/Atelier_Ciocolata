<?php
require_once __DIR__ . "/php/db.php";
require_once __DIR__ . "/php/helpers.php";

// Panoul admin necesita autentificare.
requireLogin();

$user = getCurrentUser($pdo);
$admin = isAdmin($pdo);
$message = "";
$error = "";

if (!$admin) {
    $error = "Nu ai rol de admin.";
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add_product") {
        // Adminul poate adauga produse noi in catalog.
        $name = trim($_POST["name"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $price = (float) ($_POST["price"] ?? 0);
        $image = trim($_POST["image"] ?? "");
        $stock = (int) ($_POST["stock"] ?? 0);

        if ($name === "" || $price <= 0 || $stock < 0) {
            $error = "Completeaza corect numele, pretul si stocul.";
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO products (name, description, price, image, stock) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$name, $description, $price, $image, $stock]);
            $message = "Produs adaugat.";
        }
    }

    if ($action === "delete_product") {
        // Stergerea este blocata daca produsul apare deja intr-o comanda.
        $productId = (int) ($_POST["product_id"] ?? 0);

        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $message = "Produs sters.";
        } catch (Throwable $exception) {
            $error = "Produsul nu poate fi sters daca exista deja intr-o comanda.";
        }
    }

    if ($action === "validate_order") {
        // Validarea elimina comanda din tabelele citite si de exportul ERP/XML.
        $activeView = "orders";
        $orderId = (int) ($_POST["order_id"] ?? 0);

        if ($orderId <= 0) {
            $error = "Comanda selectata nu este valida.";
        } else {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
                $stmt->execute([$orderId]);

                $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                $stmt->execute([$orderId]);

                if ($stmt->rowCount() === 0) {
                    $pdo->rollBack();
                    $error = "Comanda nu mai exista.";
                } else {
                    $pdo->commit();
                    $message = "Comanda validata si stearsa din lista si ERP/XML.";
                }
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $error = "Comanda nu a putut fi validata.";
            }
        }
    }
}

$products = [];
$users = [];
$orders = [];
$itemsByOrder = [];
$usersById = [];
$stats = ["users" => 0, "products" => 0, "orders" => 0];

if ($admin) {
    $stats = [
        "users" => (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        "products" => (int) $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
        "orders" => (int) $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    ];

    $users = $pdo
        ->query("SELECT id, username, money, role FROM users ORDER BY id ASC")
        ->fetchAll();

    $usersById = [];
    foreach ($users as $userItem) {
        $usersById[(int) $userItem["id"]] = $userItem;
    }

    $products = $pdo
        ->query("SELECT id, name, description, price, image, stock FROM products ORDER BY id DESC")
        ->fetchAll();

    $orders = $pdo
        ->query(
            "SELECT o.id, o.user_id, o.total, o.status, o.created_at, u.username
             FROM orders o
             JOIN users u ON u.id = o.user_id
             ORDER BY o.created_at DESC"
        )
        ->fetchAll();

    if ($orders) {
        $orderIds = array_column($orders, "id");
        $placeholders = implode(",", array_fill(0, count($orderIds), "?"));

        $stmt = $pdo->prepare(
            "SELECT oi.order_id, oi.quantity, oi.price, p.name
             FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id IN ($placeholders)
             ORDER BY oi.id ASC"
        );
        $stmt->execute($orderIds);

        foreach ($stmt->fetchAll() as $item) {
            $itemsByOrder[(int) $item["order_id"]][] = $item;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>⚙️ Admin</title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body>

<header>
  <h1>⚙️ Admin Panel</h1>

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
  <?php if ($message): ?>
    <p class="message success"><?= e($message) ?></p>
  <?php endif; ?>

  <?php if ($error): ?>
    <p class="message error"><?= e($error) ?></p>
  <?php endif; ?>

  <h2>📊 Statistici</h2>
  <div id="stats" class="card">
    <p>👥 Utilizatori: <?= $stats["users"] ?></p>
    <p>🍫 Produse: <?= $stats["products"] ?></p>
    <p>📦 Comenzi: <?= $stats["orders"] ?></p>

    <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:12px;">
      <a class="button-link" href="#admin-users">👥 Vezi utilizatorii</a>
      <a class="button-link" href="#admin-products">🍫 Vezi produsele</a>
      <a class="button-link" href="#admin-orders">📦 Vezi comenzile</a>
    </div>
  </div>

  <h2>🍫 Produse existente</h2>
  <div id="admin-products">
    <?php if (!$products): ?>
      <div class="card">
        <p>Nu exista produse.</p>
      </div>
    <?php endif; ?>

    <?php foreach ($products as $product): ?>
      <div class="card fade-in">
        <?php if (!empty($product["image"])): ?>
          <img src="<?= e($product["image"]) ?>" alt="<?= e($product["name"]) ?>" style="max-height:120px;object-fit:cover;">
        <?php endif; ?>

        <h3><?= e($product["name"]) ?></h3>
        <p><?= number_format((float) $product["price"], 2) ?> RON</p>
        <p>Stoc: <?= (int) $product["stock"] ?></p>

        <?php if (!$error || $admin): ?>
          <form method="post">
            <input type="hidden" name="action" value="delete_product">
            <input type="hidden" name="product_id" value="<?= (int) $product["id"] ?>">
            <button type="submit">🗑️ Sterge produs</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($admin): ?>
    <div class="card">
      <h2>➕ Adauga produs</h2>

      <form method="post">
        <input type="hidden" name="action" value="add_product">
        <input name="name" placeholder="Nume produs" required>
        <input name="price" type="number" step="0.01" min="0.01" placeholder="Pret" required>
        <input name="stock" type="number" min="0" placeholder="Stoc" required>
        <input name="image" placeholder="URL imagine sau cale locala">
        <input name="description" placeholder="Descriere">

        <button type="submit">✅ Adauga</button>
      </form>
    </div>
  <?php endif; ?>

  <div id="admin-users" class="card">
    <h2>👥 Utilizatori existenti</h2>

    <?php if (!$users): ?>
      <p>Nu exista utilizatori.</p>
    <?php else: ?>
      <?php foreach ($users as $userItem): ?>
        <div class="card fade-in">
          <h3><?= e($userItem["username"]) ?></h3>
          <p>Rol: <?= e($userItem["role"] ?? "client") ?></p>
          <p>Bani: <?= number_format((float) $userItem["money"], 2) ?> RON</p>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div id="admin-orders" class="card">
    <h2>📦 Comenzi existente</h2>

    <?php if (!$orders): ?>
      <p>Nu exista comenzi.</p>
    <?php else: ?>
      <?php foreach ($orders as $order): ?>
        <div class="card fade-in">
          <h3>Comanda #<?= (int) $order["id"] ?></h3>
          <p>Utilizator: <?= e($usersById[(int) $order["user_id"]]["username"] ?? $order["username"] ?? "Necunos") ?></p>
          <p>Status: <?= e($order["status"]) ?></p>
          <p>Total: <?= number_format((float) $order["total"], 2) ?> RON</p>
          <p>Data: <?= e($order["created_at"]) ?></p>

          <?php foreach ($itemsByOrder[(int) $order["id"]] ?? [] as $item): ?>
            <p>
              <?= e($item["name"]) ?>:
              <?= (int) $item["quantity"] ?> x <?= number_format((float) $item["price"], 2) ?> RON
            </p>
          <?php endforeach; ?>

          <form method="post" style="display:inline-block; margin-right:8px;">
            <input type="hidden" name="action" value="validate_order">
            <input type="hidden" name="order_id" value="<?= (int) $order["id"] ?>">
            <button type="submit">✅ Valideaza comanda</button>
          </form>

          <form method="post" style="display:inline-block;">
            <input type="hidden" name="action" value="delete_order">
            <input type="hidden" name="order_id" value="<?= (int) $order["id"] ?>">
            <button type="submit">🗑️ Sterge comanda</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

</body>
</html>
