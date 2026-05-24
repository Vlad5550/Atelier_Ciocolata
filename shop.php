<?php
require_once __DIR__ . "/php/db.php";
require_once __DIR__ . "/php/helpers.php";

// Magazinul poate fi accesat doar dupa autentificare.
requireLogin();

$user = getCurrentUser($pdo);
$message = "";
$error = "";

if (!isset($_SESSION["cart"])) {
    // Cosul este pastrat in sesiune pana la finalizarea comenzii.
    $_SESSION["cart"] = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "add_to_cart") {
    // Butonul de produs trimite id-ul produsului care trebuie adaugat.
    $productId = (int) ($_POST["product_id"] ?? 0);

    $stmt = $pdo->prepare("SELECT id, name, stock FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        $error = "Produsul nu exista.";
    } elseif ((int) $product["stock"] <= 0) {
        // Nu lasam utilizatorul sa adauge produse fara stoc.
        $error = "Produsul nu mai este in stoc.";
    } else {
        $currentQty = (int) ($_SESSION["cart"][$productId] ?? 0);

        if ($currentQty >= (int) $product["stock"]) {
            // Cantitatea din cos nu poate depasi stocul disponibil.
            $error = "Ai deja in cos tot stocul disponibil pentru acest produs.";
        } else {
            $_SESSION["cart"][$productId] = $currentQty + 1;
            $message = "Produs adaugat in cos.";
        }
    }
}

// Produsele sunt afisate de la cele mai noi la cele mai vechi.
$stmt = $pdo->query("SELECT id, name, description, price, image, stock FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🛍️ Magazin</title>
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<header>
  <h1>🛍️ Magazin Ciocolata</h1>
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
  <div class="card">
    <h2>🍫 100% Cacao Alcalina Premium</h2>
    <p>Produsele sunt create manual si cu drag din partea casei.</p>
  </div>

  <?php if ($message): ?>
    <p class="message success"><?= e($message) ?></p>
  <?php endif; ?>

  <?php if ($error): ?>
    <p class="message error"><?= e($error) ?></p>
  <?php endif; ?>

  <h2>🧺 Produse disponibile</h2>

  <div id="product-list">
    <?php if (!$products): ?>
      <div class="card">
        <p>Nu exista produse in baza de date.</p>
      </div>
    <?php endif; ?>

    <?php foreach ($products as $product): ?>
      <div class="card fade-in">
        <?php if (!empty($product["image"])): ?>
          <img src="<?= e($product["image"]) ?>" alt="<?= e($product["name"]) ?>">
        <?php endif; ?>

        <h3><?= e($product["name"]) ?></h3>
        <p><?= e($product["description"]) ?></p>
        <p><strong><?= number_format((float) $product["price"], 2) ?> RON</strong></p>
        <p>Stoc: <?= (int) $product["stock"] ?></p>

        <form method="post">
          <input type="hidden" name="action" value="add_to_cart">
          <input type="hidden" name="product_id" value="<?= (int) $product["id"] ?>">
          <button type="submit" <?= (int) $product["stock"] <= 0 ? "disabled" : "" ?>>
            🛒 Adauga in cos
          </button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>
</main>

</body>
</html>
