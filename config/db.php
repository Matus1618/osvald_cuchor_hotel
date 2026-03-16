<?php

$host = 'db.r6.websupport.sk:3306';
$db = 'osvald_cuchor';
$user = 'anoano';
$pass = '8,d{@,tl[Z[mGknak*+S';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>