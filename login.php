<?php
session_start();
require_once 'config/db.php';
require_once 'classes/User.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $userManager = new User($pdo);

    if ($userManager->login($email, $password)) {
        // Kontrola roly priamo po úspešnom prihlásení
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            header("Location: admin.php"); // Ak je admin, hoď ho do správy rezervácií
        } else {
            header("Location: index.php"); // Ak je zákazník, hoď ho na hlavnú stránku
        }
        exit;
    } else {
        $error = "Zadali ste nesprávny e-mail alebo heslo!";
    }
}
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prihlásenie | Hotel LUXURY</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="navbar-brand" style="padding: 0; display: flex; align-items: center;">
            <img src="logo.png" alt="LUXURY Logo" style="height: 50px; width: auto;">
        </a>
        <div class="nav-links">
            <a href="login.php" class="btn-outline">Prihlásenie</a>
            <a href="register.php" class="btn-gold">Registrácia</a>
        </div>
    </nav>

    <div class="booking-panel" style="display: block; max-width: 400px; margin: 80px auto;">
        <h2 style="text-align: center; margin-bottom: 25px;">Prihlásenie</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom: 15px; padding: 10px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group" style="margin-bottom: 15px;">
                <label>E-mail</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label>Heslo</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn-gold" style="width: 100%; border: none; cursor: pointer;">Prihlásiť sa</button>
        </form>

        <p style="text-align: center; margin-top: 20px; font-size: 14px; color: rgba(255,255,255,0.7);">
            Nemáte účet? <a href="register.php" style="color: #d4af37;">Zaregistrujte sa tu</a>.
        </p>
    </div>

</body>
</html>