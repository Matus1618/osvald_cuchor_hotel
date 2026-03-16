<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/db.php';
require_once 'classes/Booking.php';

// Ochrana
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die("Kritická chyba: Formulár nebol odoslaný.");
}
if (!isset($_SESSION['user_id'])) {
    die("Kritická chyba: Nie ste prihlásený.");
}

// Kontrola prijatia nových údajov
if (!isset($_POST['room_type'], $_POST['check_in'], $_POST['check_out'], $_POST['board_type'])) {
    die("Kritická chyba: Z formulára neprišli všetky údaje. Skontrolujte index.php.");
}

$user_id = $_SESSION['user_id'];
$room_type = $_POST['room_type']; // Máme typ namiesto presnej izby
$check_in = $_POST['check_in'];
$check_out = $_POST['check_out'];
$board_type = $_POST['board_type'];
$has_pet = isset($_POST['has_pet']) ? 1 : 0;

// Ošetrenie textového poľa
$special_requests = isset($_POST['special_requests']) ? trim($_POST['special_requests']) : null;

$date_in = new DateTime($check_in);
$date_out = new DateTime($check_out);

if ($date_in >= $date_out) {
    $_SESSION['booking_error'] = "Dátum odchodu musí byť neskôr ako dátum príchodu.";
    header("Location: index.php");
    exit;
}

$nights = $date_in->diff($date_out)->days;

try {
    $bookingManager = new Booking($pdo);

    // 1. Automatické vyhľadanie prvej voľnej izby daného typu
    $availableRoom = $bookingManager->findAvailableRoom($room_type, $check_in, $check_out);

    // Ak sa nič nenašlo, vráti chybu na hlavnú stránku
    if (!$availableRoom) {
        $_SESSION['booking_error'] = "Je nám ľúto, všetky izby typu '" . ucfirst($room_type) . "' sú v tomto termíne už obsadené.";
        header("Location: index.php");
        exit;
    }

    $room_id = $availableRoom['id'];

    // 2. Kontrola, či vybraná nájdená izba povoľuje zvieratá (ak ho zákazník má)
    if ($has_pet && !$availableRoom['pets_allowed']) {
        $_SESSION['booking_error'] = "Do najbližšej voľnej izby nie sú povolené domáce zvieratá. Skúste zmeniť termín.";
        header("Location: index.php");
        exit;
    }

    // 3. Výpočet ceny z nájdenej izby
    $total_price = $nights * $availableRoom['price_per_night'];
    if ($board_type == 'half_board') $total_price += ($nights * 15);
    if ($board_type == 'full_board') $total_price += ($nights * 30);
    if ($has_pet) $total_price += 30;

    // 4. Oficiálny zápis do databázy (vrátane požiadaviek)
    $bookingManager->createBooking($user_id, $room_id, $check_in, $check_out, $board_type, $has_pet, $total_price, $special_requests);

    // Presmerovanie do profilu
    $_SESSION['booking_success'] = "Rezervácia úspešná! Systém Vám pridelil voľnú izbu automaticky. Celková cena: $total_price €.";
    header("Location: profile.php");
    exit;

} catch (PDOException $e) {
    die("Kritická chyba databázy pri spracovaní: " . $e->getMessage());
}
?>