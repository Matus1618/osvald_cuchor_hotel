<?php
session_start();
require_once 'config/db.php';

// Skontrolujeme, či bol odoslaný formulár a či je používateľ prihlásený
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);

    // Ochrana pred vložením prázdnych alebo neplatných dát
    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, rating, comment) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $rating, $comment]);
    }
}

// Po uložení vrátime používateľa späť na sekciu recenzií
header("Location: index.php#recenzie");
exit;
?>