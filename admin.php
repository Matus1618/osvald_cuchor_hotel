<?php
session_start();
require_once 'config/db.php';
require_once 'classes/Booking.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$userName = $_SESSION['full_name'];
$bookingManager = new Booking($pdo);
$message = '';

// Spracovanie POST požiadaviek
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['booking_id'], $_POST['action'])) {
    $booking_id = $_POST['booking_id'];
    
    if ($_POST['action'] === 'update' && isset($_POST['new_status'])) {
        $new_status = $_POST['new_status'];
        if ($bookingManager->updateBookingStatus($booking_id, $new_status)) {
            $message = "Stav rezervácie #$booking_id bol úspešne zmenený.";
        }
    } elseif ($_POST['action'] === 'delete') {
        if ($bookingManager->deleteBooking($booking_id)) {
            $message = "Rezervácia #$booking_id bola natrvalo vymazaná.";
        }
    }
}

try {
    $all_bookings = $bookingManager->getAllBookings();
} catch (PDOException $e) {
    die("Chyba databázy: " . $e->getMessage());
}

// 1. Výpočet štatistík (Opravené: počíta len aktívne)
$total_bookings = 0;
$pending_count = 0;
$total_revenue = 0;

foreach ($all_bookings as $b) {
    if ($b['status'] != 'cancelled') {
        $total_bookings++; // Zaráta len tie, ktoré nie sú zrušené
    }
    if ($b['status'] == 'pending') {
        $pending_count++;
    }
    if ($b['status'] == 'confirmed') {
        $total_revenue += $b['total_price'];
    }
}

// 2. Filtrovanie
$current_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$filtered_bookings = [];

foreach ($all_bookings as $b) {
    if ($current_filter === 'all' || $b['status'] === $current_filter) {
        $filtered_bookings[] = $b;
    }
}
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrácia | Hotel LUXURY</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container { max-width: 1300px; margin: 40px auto; padding: 30px; background-color: #151515; border-radius: 8px; border: 1px solid #333; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { border: 1px solid #333; padding: 12px; text-align: left; vertical-align: middle; }
        th { background-color: #0a0a0a; color: #d4af37; font-family: 'Cinzel', serif; letter-spacing: 1px; }
        td { background-color: #111; color: #fff; }
        .status-pending { color: #f39c12; font-weight: bold; }
        .status-confirmed { color: #2ecc71; font-weight: bold; }
        .status-cancelled { color: #e74c3c; font-weight: bold; }
        .action-form { display: inline-block; margin-right: 5px; }
        .action-select { padding: 5px; background: #0a0a0a; color: #fff; border: 1px solid #444; border-radius: 4px; }
        .btn-small { padding: 6px 10px; font-size: 12px; border-radius: 4px; cursor: pointer; border: none; }
        .btn-danger { background-color: #e74c3c; color: white; }
        .req-text { font-style: italic; color: #ddd; max-width: 200px; word-wrap: break-word; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #0a0a0a; border: 1px solid #333; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-card h3 { color: #aaa; font-size: 12px; text-transform: uppercase; margin-bottom: 10px; font-family: 'Montserrat', sans-serif; letter-spacing: 1px;}
        .stat-card .value { color: #d4af37; font-size: 28px; font-weight: bold; }
        .filter-bar { display: flex; justify-content: space-between; align-items: center; background: #0a0a0a; border: 1px solid #333; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="navbar-brand" style="padding: 0; display: flex; align-items: center;">
            <img src="assets/img/logo.png" alt="LUXURY Logo" style="height: 50px; width: auto;">
        </a>
        <div class="nav-links">
            <span>Admin: <?php echo htmlspecialchars($userName); ?></span>
            <a href="admin_rooms.php" class="btn-outline">Správa izieb</a>
            <a href="index.php" class="btn-outline">Zákaznícky web</a>
            <a href="logout.php" class="btn-gold">Odhlásiť sa</a>
        </div>
    </nav>

    <div class="admin-container">
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Aktívne rezervácie</h3>
                <div class="value"><?php echo $total_bookings; ?></div>
            </div>
            <div class="stat-card">
                <h3>Čaká na schválenie</h3>
                <div class="value" style="color: #f39c12;"><?php echo $pending_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>Potvrdený zisk</h3>
                <div class="value"><?php echo number_format($total_revenue, 2, ',', ' '); ?> €</div>
            </div>
        </div>

        <?php if ($message): ?>
            <div style="background-color: rgba(212, 175, 55, 0.1); border: 1px solid #d4af37; color: #d4af37; padding: 15px; text-align: center; margin-bottom: 20px; border-radius: 4px;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="filter-bar">
            <h2 style="font-size: 20px; margin: 0;">Zoznam rezervácií</h2>
            <form method="GET" action="admin.php" style="display: flex; gap: 10px; align-items: center;">
                <label style="font-size: 14px; color: #aaa;">Filtrovať podľa stavu:</label>
                <select name="filter" class="action-select" onchange="this.form.submit()">
                    <option value="all" <?php if($current_filter == 'all') echo 'selected'; ?>>Všetky</option>
                    <option value="pending" <?php if($current_filter == 'pending') echo 'selected'; ?>>Čakajúce</option>
                    <option value="confirmed" <?php if($current_filter == 'confirmed') echo 'selected'; ?>>Potvrdené</option>
                    <option value="cancelled" <?php if($current_filter == 'cancelled') echo 'selected'; ?>>Zrušené</option>
                </select>
            </form>
        </div>

        <?php if (count($filtered_bookings) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Zákazník</th>
                        <th>Izba</th>
                        <th>Termín</th>
                        <th>Cena</th>
                        <th>Požiadavky</th>
                        <th>Aktuálny stav</th>
                        <th>Akcie</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filtered_bookings as $booking): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($booking['id']); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($booking['full_name']); ?></strong><br>
                                <span style="font-size: 12px; color: #aaa;"><?php echo htmlspecialchars($booking['email']); ?></span>
                            </td>
                            <td>Izba č. <?php echo htmlspecialchars($booking['room_number']); ?></td>
                            <td>
                                <?php echo date('d.m.Y', strtotime($booking['check_in'])); ?> - 
                                <?php echo date('d.m.Y', strtotime($booking['check_out'])); ?>
                            </td>
                            <td><strong style="color: #d4af37;"><?php echo htmlspecialchars($booking['total_price']); ?> €</strong></td>
                            <td class="req-text">
                                <?php echo !empty($booking['special_requests']) ? nl2br(htmlspecialchars($booking['special_requests'])) : '<span style="color: #666;">Žiadne</span>'; ?>
                            </td>
                            <td class="status-<?php echo $booking['status']; ?>">
                                <?php 
                                    if ($booking['status'] == 'pending') echo 'Čaká';
                                    elseif ($booking['status'] == 'confirmed') echo 'Potvrdená';
                                    elseif ($booking['status'] == 'cancelled') echo 'Zrušená';
                                ?>
                            </td>
                            <td style="white-space: nowrap;">
                                
                                <?php if ($booking['status'] != 'cancelled'): ?>
                                    <form method="POST" action="admin.php" class="action-form">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="action" value="update">
                                        <select name="new_status" class="action-select">
                                            <option value="pending" <?php if($booking['status'] == 'pending') echo 'selected'; ?>>Čaká</option>
                                            <option value="confirmed" <?php if($booking['status'] == 'confirmed') echo 'selected'; ?>>Potvrdiť</option>
                                            <option value="cancelled">Zrušiť</option>
                                        </select>
                                        <button type="submit" class="btn-gold btn-small" style="border: none; cursor: pointer;">Uložiť</button>
                                    </form>
                                <?php else: ?>
                                    <span style="display: inline-block; padding: 6px 10px; font-size: 12px; color: #e74c3c; border: 1px solid #e74c3c; border-radius: 4px; margin-right: 5px; background: rgba(231, 76, 60, 0.1);">Zablokované</span>
                                <?php endif; ?>

                                <form method="POST" action="admin.php" class="action-form" onsubmit="return confirm('Naozaj chcete trvalo vymazať túto rezerváciu?');">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn-danger btn-small">Vymazať</button>
                                </form>
                                
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: rgba(255,255,255,0.7); margin-top: 30px;">Zvolenému filtru nezodpovedajú žiadne rezervácie.</p>
        <?php endif; ?>
    </div>

</body>
</html>