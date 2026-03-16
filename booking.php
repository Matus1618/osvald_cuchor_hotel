<?php
// booking.php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/db.php';
require_once 'classes/Booking.php';

if (!isset($_SESSION['user_id'])) {
    die("Chyba prístupu: Pre zobrazenie tohto formulára musíte byť prihlásený. <a href='login.php'>Prihlásiť sa</a>");
}

try {
    // Vytvorenie objektu rezervácie a načítanie izieb
    $bookingManager = new Booking($pdo);
    $rooms = $bookingManager->getAllRooms();
} catch (PDOException $e) {
    die("Chyba databázy pri načítaní izieb: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Rezervácia izby | Hotel LUXURY</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 900px; margin: 0 auto; }
        .room-card { border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; border-radius: 5px; }
        .pet-friendly { color: green; font-weight: bold; }
        .form-group { margin-bottom: 15px; }
        button { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
    </style>
</head>
<body>
    <a href="index.php">Späť na domovskú stránku</a>
    <h2>Vytvorenie rezervácie</h2>

    <form action="process_booking.php" method="POST">
        <div class="form-group">
            <label>Dátum príchodu (Check-in):</label><br>
            <input type="date" name="check_in" required>
        </div>
        
        <div class="form-group">
            <label>Dátum odchodu (Check-out):</label><br>
            <input type="date" name="check_out" required>
        </div>

        <div class="form-group">
            <label>Strava:</label><br>
            <select name="board_type" required>
                <option value="none">Bez stravy</option>
                <option value="half_board">Polpenzia</option>
                <option value="full_board">Plná penzia</option>
            </select>
        </div>

        <div class="form-group">
            <label>Domáce zviera (príplatok 30€):</label><br>
            <input type="checkbox" name="has_pet" value="1"> Áno, mám zviera
        </div>

        <h3>Vyberte si izbu:</h3>
        <?php foreach ($rooms as $room): ?>
            <div class="room-card">
                <input type="radio" name="room_id" value="<?php echo htmlspecialchars($room['id']); ?>" required>
                <strong>Izba č. <?php echo htmlspecialchars($room['room_number']); ?></strong> 
                (Typ: <?php echo htmlspecialchars($room['type']); ?>) - 
                Cena: <?php echo htmlspecialchars($room['price_per_night']); ?> €/noc
                <?php if ($room['pets_allowed']): ?>
                    <span class="pet-friendly">- Zvieratá povolené</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <br>
        <button type="submit">Pokračovať k zhrnutiu rezervácie</button>
    </form>
</body>
</html>