<?php
session_start();
require_once 'config/db.php';
require_once 'classes/Booking.php';

// Ochrana prístupu - iba admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$userName = $_SESSION['full_name'];
$bookingManager = new Booking($pdo);
$message = '';
$error = '';

// Spracovanie formulárov (Pridať, Upraviť, Vymazať)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $room_number = trim($_POST['room_number']);
        $type = $_POST['type'];
        $beds_count = (int)$_POST['beds_count'];
        $price = (float)$_POST['price_per_night'];
        $pets = isset($_POST['pets_allowed']) ? 1 : 0;
        
        try {
            if ($bookingManager->addRoom($room_number, $type, $beds_count, $price, $pets)) {
                $message = "Nová izba (č. $room_number) bola úspešne pridaná.";
            }
        } catch (PDOException $e) {
            $error = "Chyba pri pridávaní izby (možno číslo izby už existuje).";
        }
    } 
    elseif ($_POST['action'] === 'update_price' && isset($_POST['room_id'], $_POST['new_price'])) {
        $room_id = $_POST['room_id'];
        $new_price = (float)$_POST['new_price'];
        if ($bookingManager->updateRoomPrice($room_id, $new_price)) {
            $message = "Cena izby bola úspešne aktualizovaná.";
        } else {
            $error = "Chyba pri aktualizácii ceny.";
        }
    } 
    elseif ($_POST['action'] === 'delete' && isset($_POST['room_id'])) {
        $room_id = $_POST['room_id'];
        try {
            if ($bookingManager->deleteRoom($room_id)) {
                $message = "Izba bola úspešne vymazaná zo systému.";
            }
        } catch (PDOException $e) {
            $error = "Izbu nie je možné vymazať, pretože k nej už existujú vytvorené rezervácie.";
        }
    }
}

// Načítanie všetkých izieb
$rooms = [];
try {
    $rooms = $bookingManager->getAllRooms();
} catch (PDOException $e) {
    $error = "Chyba pri načítaní izieb: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Správa izieb | Hotel LUXURY</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container { max-width: 1200px; margin: 40px auto; padding: 30px; background-color: #151515; border-radius: 8px; border: 1px solid #333; }
        .grid-layout { display: grid; grid-template-columns: 300px 1fr; gap: 30px; }
        .add-panel { background: #0a0a0a; padding: 20px; border: 1px solid #333; border-radius: 8px; height: fit-content; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { border: 1px solid #333; padding: 12px; text-align: left; vertical-align: middle; }
        th { background-color: #0a0a0a; color: #d4af37; font-family: 'Cinzel', serif; letter-spacing: 1px; }
        td { background-color: #111; color: #fff; }
        .action-form { display: inline-flex; align-items: center; gap: 5px; margin-right: 5px; }
        .action-input { padding: 6px; width: 80px; background: #0a0a0a; color: #fff; border: 1px solid #444; border-radius: 4px; }
        .btn-small { padding: 6px 10px; font-size: 12px; border-radius: 4px; cursor: pointer; border: none; }
        .btn-danger { background-color: #e74c3c; color: white; }
        .btn-danger:hover { background-color: #c0392b; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; text-align: center; }
        .alert-success { background-color: rgba(212, 175, 55, 0.1); border: 1px solid #d4af37; color: #d4af37; }
        .alert-error { background-color: rgba(255, 0, 0, 0.1); border: 1px solid red; color: #ff4d4d; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="navbar-brand" style="padding: 0; display: flex; align-items: center;">
            <img src="assets/img/logo.png" alt="LUXURY Logo" style="height: 50px; width: auto;">
        </a>
        <div class="nav-links">
            <a href="admin.php" class="btn-outline">Správa rezervácií</a>
            <a href="index.php" class="btn-outline">Zákaznícky web</a>
            <a href="logout.php" class="btn-gold">Odhlásiť sa</a>
        </div>
    </nav>

    <div class="admin-container">
        <h2 style="margin-bottom: 20px; text-align: center;">Správa izieb</h2>
        
        <?php if ($message): ?> <div class="alert alert-success"><?php echo $message; ?></div> <?php endif; ?>
        <?php if ($error): ?> <div class="alert alert-error"><?php echo $error; ?></div> <?php endif; ?>

        <div class="grid-layout">
            
            <div class="add-panel">
                <h3 style="margin-bottom: 15px; font-size: 18px;">Pridať novú izbu</h3>
                <form method="POST" action="admin_rooms.php">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Číslo izby (napr. 401)</label>
                        <input type="text" name="room_number" required>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Typ izby</label>
                        <select name="type" required>
                            <option value="single">Single</option>
                            <option value="double">Double</option>
                            <option value="suite">Suite</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Počet lôžok</label>
                        <input type="number" name="beds_count" min="1" max="10" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Cena za noc (€)</label>
                        <input type="number" name="price_per_night" step="0.01" min="0" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: inline-block; cursor: pointer;">
                            <input type="checkbox" name="pets_allowed" value="1" style="width: auto; margin-right: 5px;"> Zvieratá povolené
                        </label>
                    </div>

                    <button type="submit" class="btn-gold" style="width: 100%; border: none;">Pridať do ponuky</button>
                </form>
            </div>

            <div>
                <?php if (count($rooms) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Číslo</th>
                                <th>Typ</th>
                                <th>Lôžka</th>
                                <th>Zvieratá</th>
                                <th>Cena (€/noc)</th>
                                <th>Vymazať</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rooms as $room): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($room['room_number']); ?></strong></td>
                                    <td><?php echo ucfirst(htmlspecialchars($room['type'])); ?></td>
                                    <td><?php echo htmlspecialchars($room['beds_count']); ?></td>
                                    <td><?php echo $room['pets_allowed'] ? '<span style="color: #2ecc71;">Áno</span>' : '<span style="color: #e74c3c;">Nie</span>'; ?></td>
                                    <td>
                                        <form method="POST" action="admin_rooms.php" class="action-form">
                                            <input type="hidden" name="action" value="update_price">
                                            <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                            <input type="number" name="new_price" step="0.01" class="action-input" value="<?php echo htmlspecialchars($room['price_per_night']); ?>" required>
                                            <button type="submit" class="btn-gold btn-small">Uložiť</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" action="admin_rooms.php" onsubmit="return confirm('Naozaj chcete vymazať izbu č. <?php echo $room['room_number']; ?>?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                            <button type="submit" class="btn-danger btn-small">Vymazať</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: rgba(255,255,255,0.7);">Zatiaľ nemáte pridané žiadne izby.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>

</body>
</html>