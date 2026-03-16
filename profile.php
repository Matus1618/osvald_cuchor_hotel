<?php
session_start();
require_once 'config/db.php';
require_once 'classes/Booking.php';
require_once 'classes/User.php'; // Pridané načítanie triedy User

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$bookingManager = new Booking($pdo);
$userManager = new User($pdo);

// Spracovanie POST požiadaviek (Zrušenie rezervácie alebo Úprava profilu)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] == 'cancel_booking') {
        $booking_id = $_POST['booking_id'];
        if ($bookingManager->cancelBookingByUser($booking_id, $user_id)) {
            $_SESSION['booking_success'] = "Rezervácia #$booking_id bola úspešne zrušená z vašej strany.";
            header("Location: profile.php");
            exit;
        }
    } 
    elseif ($_POST['action'] == 'update_profile') {
        $new_name = trim($_POST['full_name']);
        $new_password = $_POST['new_password'] ?? null;
        
        if ($userManager->updateProfile($user_id, $new_name, $new_password)) {
            $_SESSION['full_name'] = $new_name; // Aktualizácia mena v hlavičke
            $_SESSION['profile_success'] = "Vaše profilové údaje boli úspešne aktualizované.";
            header("Location: profile.php");
            exit;
        }
    }
}

// Načítanie dát pre zobrazenie
try {
    $my_bookings = $bookingManager->getUserBookings($user_id);
    $userData = $userManager->getUserById($user_id);
} catch (PDOException $e) {
    die("Chyba databázy: " . $e->getMessage());
}

$userName = $_SESSION['full_name'];
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Môj profil | Hotel LUXURY</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container { max-width: 1100px; margin: 50px auto; padding: 30px; background-color: #151515; border-radius: 8px; border: 1px solid #333; }
        .grid-profile { display: grid; grid-template-columns: 1fr 300px; gap: 30px; }
        
        /* Tabuľka rezervácií */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 15px; text-align: left; }
        th { background-color: #0a0a0a; color: #d4af37; font-family: 'Cinzel', serif; letter-spacing: 1px; }
        td { background-color: #111; color: #fff; }
        .status-pending { color: #f39c12; font-weight: bold; }
        .status-confirmed { color: #2ecc71; font-weight: bold; }
        .status-cancelled { color: #e74c3c; font-weight: bold; }
        .btn-small { padding: 6px 12px; font-size: 12px; cursor: pointer; border: none; border-radius: 4px; }
        .btn-danger { background-color: #e74c3c; color: white; }
        
        /* Formulár na úpravu profilu */
        .edit-panel { background: #0a0a0a; padding: 20px; border: 1px solid #333; border-radius: 8px; height: fit-content; }
        .edit-panel h3 { margin-bottom: 20px; font-size: 18px; color: #d4af37; }
        .form-group label { display: block; margin-bottom: 5px; color: #aaa; font-size: 14px; }
        .form-group input { width: 100%; padding: 10px; margin-bottom: 15px; background: #111; border: 1px solid #444; color: #fff; border-radius: 4px; }
        .form-group input:focus { border-color: #d4af37; outline: none; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="navbar-brand" style="padding: 0; display: flex; align-items: center;">
            <img src="assets/img/logo.png" alt="LUXURY Logo" style="height: 50px; width: auto;">
        </a>
        <div class="nav-links">
            <span>Ahoj, <?php echo htmlspecialchars($userName); ?></span>
            <a href="index.php" class="btn-outline">Domov</a>
            <a href="logout.php" class="btn-gold">Odhlásiť sa</a>
        </div>
    </nav>

    <div class="profile-container">
        
        <?php if (isset($_SESSION['booking_success'])): ?>
            <div style="background-color: rgba(0, 255, 0, 0.1); border: 1px solid green; color: #4CAF50; padding: 15px; text-align: center; margin-bottom: 20px; border-radius: 4px;">
                <?php echo $_SESSION['booking_success']; unset($_SESSION['booking_success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['profile_success'])): ?>
            <div style="background-color: rgba(212, 175, 55, 0.1); border: 1px solid #d4af37; color: #d4af37; padding: 15px; text-align: center; margin-bottom: 20px; border-radius: 4px;">
                <?php echo $_SESSION['profile_success']; unset($_SESSION['profile_success']); ?>
            </div>
        <?php endif; ?>

        <div class="grid-profile">
            <div>
                <h2 style="margin-bottom: 20px;">Moje rezervácie</h2>
                <?php if (count($my_bookings) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Izba č.</th>
                                <th>Typ</th>
                                <th>Termín</th>
                                <th>Cena</th>
                                <th>Stav</th>
                                <th>Akcia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_bookings as $booking): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($booking['room_number']); ?></td>
                                    <td><?php echo ucfirst(htmlspecialchars($booking['type'])); ?></td>
                                    <td>
                                        <?php echo date('d.m.Y', strtotime($booking['check_in'])); ?> - 
                                        <?php echo date('d.m.Y', strtotime($booking['check_out'])); ?>
                                    </td>
                                    <td><strong style="color: #d4af37;"><?php echo htmlspecialchars($booking['total_price']); ?> €</strong></td>
                                    <td class="status-<?php echo $booking['status']; ?>">
                                        <?php 
                                            if ($booking['status'] == 'pending') echo 'Čaká na schválenie';
                                            elseif ($booking['status'] == 'confirmed') echo 'Potvrdená';
                                            elseif ($booking['status'] == 'cancelled') echo 'Zrušená';
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($booking['status'] == 'pending' || $booking['status'] == 'confirmed'): ?>
                                            <form method="POST" action="profile.php" onsubmit="return confirm('Naozaj chcete zrušiť túto rezerváciu?');">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <input type="hidden" name="action" value="cancel_booking">
                                                <button type="submit" class="btn-danger btn-small">Zrušiť</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #666; font-size: 12px;">Zrušená</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: rgba(255,255,255,0.7); margin-top: 10px;">Zatiaľ nemáte žiadne rezervácie.</p>
                <?php endif; ?>
            </div>

            <div class="edit-panel">
                <h3>Nastavenia profilu</h3>
                <form method="POST" action="profile.php">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label>E-mailová adresa (nedá sa zmeniť)</label>
                        <input type="email" value="<?php echo htmlspecialchars($userData['email']); ?>" disabled style="color: #666; cursor: not-allowed;">
                    </div>

                    <div class="form-group">
                        <label>Celé meno</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($userData['full_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Nové heslo (vyplňte iba ak chcete zmeniť)</label>
                        <input type="password" name="new_password" placeholder="Zadajte nové heslo...">
                    </div>

                    <button type="submit" class="btn-gold" style="width: 100%; border: none;">Uložiť zmeny</button>
                </form>
            </div>
        </div>

    </div>

</body>
</html>