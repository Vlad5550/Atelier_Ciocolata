<?php
require_once __DIR__ . "/php/helpers.php";

// Goleste sesiunea curenta, inclusiv user-ul logat si cosul.
$_SESSION = [];
session_destroy();

header("Location: auth.php");
exit;
