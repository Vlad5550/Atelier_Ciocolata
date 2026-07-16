<?php
require_once __DIR__ . "/php/db.php";
require_once __DIR__ . "/php/helpers.php";

// Daca utilizatorul este deja logat, nu mai are nevoie de pagina de login.
if (isLoggedIn()) {
    header("Location: shop.php");
    exit;
}

$error = "";
$mode = $_GET["mode"] ?? "login";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Formularul trimite acelasi fisier pentru login si pentru register.
    $mode = $_POST["mode"] ?? "login";
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($username === "" || $password === "") {
        $error = "Completeaza username si parola.";
    } elseif ($mode === "register") {
        // La inregistrare verificam intai daca username-ul exista deja.
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $error = "Acest username exista deja.";
        } else {
            // Parola se salveaza hash-uit, nu in text simplu.
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO users (username, password, money, role) VALUES (?, ?, 500.00, 'user')"
            );
            $stmt->execute([$username, $passwordHash]);

            $_SESSION["user_id"] = (int) $pdo->lastInsertId();
            header("Location: shop.php");
            exit;
        }
    } else {
        // La login cautam utilizatorul si verificam parola cu password_verify().
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user["password"])) {
            $error = "Username sau parola gresita.";
        } else {
            $_SESSION["user_id"] = (int) $user["id"];
            header("Location: shop.php");
            exit;
        }
    }
}

if ($mode !== "register") {
    $mode = "login";
}

$isRegister = $mode === "register";
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🔐 Login / Register</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="auth-container">
    <div class="auth-box">
      <h2>🔐 Login / Register</h2>

      <div class="auth-tabs">
        <a href="auth.php?mode=login" class="<?= !$isRegister ? "active" : "" ?>">🔑 Login</a>
        <a href="auth.php?mode=register" class="<?= $isRegister ? "active" : "" ?>">📝 Register</a>
      </div>

      <?php if ($error): ?>
        <p class="message error"><?= e($error) ?></p>
      <?php endif; ?>

      <form method="post">
        <h3><?= $isRegister ? "📝 Register" : "🔑 Login" ?></h3>
        <input name="username" type="text" placeholder="<?= $isRegister ? "Username nou" : "Username" ?>" required>
        <input name="password" type="password" placeholder="<?= $isRegister ? "Parola noua" : "Parola" ?>" required>
        <input type="hidden" name="mode" value="<?= e($mode) ?>">
        <button type="submit"><?= $isRegister ? "✅ Creeaza cont" : "🚪 Intra in cont" ?></button>
      </form>
    </div>
  </div>
</body>
</html>
