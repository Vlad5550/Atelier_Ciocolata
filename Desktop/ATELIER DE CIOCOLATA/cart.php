<?php
require_once __DIR__ . "/php/db.php";
require_once __DIR__ . "/php/helpers.php";

// Cosul si checkout-ul sunt disponibile doar dupa login.
requireLogin();

$user = getCurrentUser($pdo);
$message = "";
$error = "";

if (!isset($_SESSION["cart"])) {
    // Daca nu exista inca un cos in sesiune, pornim cu unul gol.
    $_SESSION["cart"] = [];
}

function cartProducts(PDO $pdo)
{
    // Transforma id-urile din cos in produse reale citite din baza de date.
    $cart = $_SESSION["cart"] ?? [];
    $ids = array_values(array_filter(array_map("intval", array_keys($cart))));

    if (!$ids) {
        return [];
    }

    $placeholders = implode(",", array_fill(0, count($ids), "?"));
    $stmt = $pdo->prepare(
        "SELECT id, name, description, price, image, stock FROM products WHERE id IN ($placeholders)"
    );
    $stmt->execute($ids);

    $products = [];
    foreach ($stmt->fetchAll() as $product) {
        $products[(int) $product["id"]] = $product;
    }

    return $products;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if (isset($_POST["remove_product_id"])) {
        // Stergerea se face prin eliminarea produsului din sesiune.
        $productId = (int) $_POST["remove_product_id"];
        unset($_SESSION["cart"][$productId]);
        $message = "Produs eliminat din cos.";
        $action = "remove";
    } elseif ($action === "update") {
        // Actualizarea modifica toate cantitatile trimise de formular.
        foreach ($_POST["qty"] ?? [] as $productId => $qty) {
            $productId = (int) $productId;
            $qty = max(0, (int) $qty);

            if ($qty === 0) {
                unset($_SESSION["cart"][$productId]);
            } else {
                $_SESSION["cart"][$productId] = $qty;
            }
        }

        $message = "Cos actualizat.";
    }

    if ($action === "checkout") {
        // Checkout-ul verifica stocul, banii utilizatorului si apoi salveaza comanda.
        $products = cartProducts($pdo);

        if (!$products) {
            $error = "Cosul este gol.";
        } else {
            $total = 0;

            foreach ($_SESSION["cart"] as $productId => $qty) {
                $productId = (int) $productId;
                $qty = (int) $qty;

                if (!isset($products[$productId])) {
                    $error = "Un produs din cos nu mai exista.";
                    break;
                }

                if ($qty > (int) $products[$productId]["stock"]) {
                    $error = "Stoc insuficient pentru " . $products[$productId]["name"] . ".";
                    break;
                }

                $total += (float) $products[$productId]["price"] * $qty;
            }

            if (!$error && (float) $user["money"] < $total) {
                $error = "Fonduri insuficiente.";
            }

            if (!$error) {
                try {
                    // Tranzactia tine impreuna plata, comanda si scaderea stocului.
                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare("UPDATE users SET money = money - ? WHERE id = ?");
                    $stmt->execute([$total, $user["id"]]);

                    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'plasata')");
                    $stmt->execute([$user["id"], $total]);
                    $orderId = (int) $pdo->lastInsertId();

                    $itemStmt = $pdo->prepare(
                        "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)"
                    );
                    $stockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

                    foreach ($_SESSION["cart"] as $productId => $qty) {
                        $productId = (int) $productId;
                        $qty = (int) $qty;
                        $price = (float) $products[$productId]["price"];

                        $itemStmt->execute([$orderId, $productId, $qty, $price]);
                        $stockStmt->execute([$qty, $productId]);
                    }

                    $pdo->commit();
                    $_SESSION["cart"] = [];
                    $user = getCurrentUser($pdo);
                    $message = "Comanda a fost finalizata.";
                } catch (Throwable $exception) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }

                    $error = "Comanda nu a putut fi salvata.";
                }
            }
        }
    }
}

$products = cartProducts($pdo);
$total = 0;
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🛒 Cos</title>
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<header>
  <h1>🛒 Cosul tau</h1>
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
    <h2>🛒 Cos de cumparaturi</h2>
    <p>Produsele selectate sunt salvate in sesiunea PHP.</p>
  </div>

  <?php if ($message): ?>
    <p class="message success"><?= e($message) ?></p>
  <?php endif; ?>

  <?php if ($error): ?>
    <p class="message error"><?= e($error) ?></p>
  <?php endif; ?>

  <?php if (!$products): ?>
    <div class="card">
      <p>🧺 Cosul este gol.</p>
      <a class="button-link" href="shop.php">🛍️ Inapoi la magazin</a>
    </div>
  <?php else: ?>
    <form method="post" id="cart-items">
      <input type="hidden" name="action" value="update">

      <?php foreach ($_SESSION["cart"] as $productId => $qty): ?>
        <?php
          $productId = (int) $productId;
          if (!isset($products[$productId])) {
              continue;
          }

          $product = $products[$productId];
          $qty = (int) $qty;
          $lineTotal = (float) $product["price"] * $qty;
          $total += $lineTotal;
        ?>

        <div class="card cart-item">
          <?php if (!empty($product["image"])): ?>
            <img src="<?= e($product["image"]) ?>" alt="<?= e($product["name"]) ?>">
          <?php endif; ?>

          <div class="cart-info">
            <h3><?= e($product["name"]) ?></h3>
            <p><?= number_format((float) $product["price"], 2) ?> RON x <?= $qty ?></p>
            <p>Total linie: <?= number_format($lineTotal, 2) ?> RON</p>
          </div>

          <input
            name="qty[<?= $productId ?>]"
            type="number"
            min="0"
            max="<?= (int) $product["stock"] ?>"
            value="<?= $qty ?>"
          >

          <button
            type="submit"
            name="remove_product_id"
            value="<?= $productId ?>"
          >
            🗑️ Sterge
          </button>
        </div>
      <?php endforeach; ?>

      <div class="card">
        <h3>💰 Total: <?= number_format($total, 2) ?> RON</h3>
        <button type="submit" name="action" value="update">🔄 Actualizeaza cos</button>
        <button type="submit" name="action" value="checkout">✅ Finalizeaza comanda</button>
      </div>
    </form>
  <?php endif; ?>
</main>

</body>
</html>
