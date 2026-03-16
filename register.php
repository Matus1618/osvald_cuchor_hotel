<?php
session_start();
require_once 'config/db.php';
require_once 'classes/User.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $userManager = new User($pdo);

    if ($userManager->emailExists($email)) {
        $error = "Tento e-mail už je zaregistrovaný!";
    } else {
        if ($userManager->register($full_name, $email, $password)) {
            $success = "Registrácia bola úspešná! Môžete sa prihlásiť.";
        } else {
            $error = "Nastala chyba pri ukladaní do databázy.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrácia | Hotel LUXURY</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="navbar-brand" style="padding: 0; display: flex; align-items: center;">
            <img src="assets/img/logo.png" alt="LUXURY Logo" style="height: 50px; width: auto;">
        </a>
        <div class="nav-links">
            <a href="login.php" class="btn-outline">Prihlásenie</a>
            <a href="register.php" class="btn-gold">Registrácia</a>
        </div>
    </nav>

    <div class="booking-panel" style="display: block; max-width: 400px; margin: 80px auto;">
        <h2 style="text-align: center; margin-bottom: 25px;">Registrácia</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom: 15px; padding: 10px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div style="background-color: rgba(0, 255, 0, 0.1); border: 1px solid green; color: #4CAF50; padding: 15px; text-align: center; margin-bottom: 15px; border-radius: 4px;">
                <strong><?php echo $success; ?></strong><br><br>
                <a href="login.php" class="btn-gold" style="display: inline-block;">Prejsť na prihlásenie</a>
            </div>
        <?php else: ?>
            <form method="POST" action="register.php">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Celé meno</label>
                    <input type="text" name="full_name" required>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>E-mail</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label>Heslo</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" class="btn-gold" style="width: 100%; border: none; cursor: pointer;">Zaregistrovať sa</button>
            </form>
        <?php endif; ?>
        
    </div>

</body>
</html>