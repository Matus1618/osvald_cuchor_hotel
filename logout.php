<?php
// logout.php
require_once 'config/db.php';
require_once 'classes/User.php';

// Vytvorenie objektu užívateľa
$userManager = new User($pdo);

// Zavolanie metódy na vymazanie session
$userManager->logout();

// Presmerovanie na domovskú stránku
header("Location: index.php");
exit;
?>