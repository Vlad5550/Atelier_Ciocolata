<?php
// Datele necesare pentru conectarea la baza de date MySQL.
$host = "localhost";
$dbname = "ciocolata";
$user = "root";
$pass = "";

try {
    // PDO trimite interogarile catre MySQL si arunca exceptii cand apar erori.
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Eroare conectare baza de date: " . $e->getMessage());
}
