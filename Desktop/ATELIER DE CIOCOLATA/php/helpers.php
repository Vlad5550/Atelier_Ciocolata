<?php
// Sesiunea tine minte utilizatorul logat si cosul de cumparaturi.
if (session_status() === PHP_SESSION_NONE) {
    $sessionPath = __DIR__ . "/sessions";

    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0777, true);
    }

    session_save_path($sessionPath);
    session_start();
}

function e($value)
{
    // Protejeaza afisarea in HTML impotriva codului introdus de utilizator.
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function currentUserId()
{
    return $_SESSION["user_id"] ?? null;
}

function isLoggedIn()
{
    return currentUserId() !== null;
}

function requireLogin()
{
    // Paginile protejate trimit vizitatorul nelogat catre autentificare.
    if (!isLoggedIn()) {
        header("Location: auth.php");
        exit;
    }
}

function getCurrentUser(PDO $pdo)
{
    // Cauta in baza de date utilizatorul salvat in sesiune.
    if (!isLoggedIn()) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT id, username, money, role FROM users WHERE id = ?");
    $stmt->execute([currentUserId()]);

    return $stmt->fetch() ?: null;
}

function isNitaAdmin(PDO $pdo)
{
    $user = getCurrentUser($pdo);

    // In proiect, adminul principal este utilizatorul NitaVlad.
    return $user && $user["username"] === "NitaVlad" && $user["role"] === "admin";
}

function isAdmin(PDO $pdo)
{
    $user = getCurrentUser($pdo);

    // Verifica rolul general de admin din baza de date.
    return $user && $user["role"] === "admin";
}
