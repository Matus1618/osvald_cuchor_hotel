<?php
// classes/User.php

class User {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Funkcia na prihlásenie
    public function login($email, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        return false;
    }

    // Funkcia na overenie, či e-mail už existuje
    public function emailExists($email) {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    // Funkcia na registráciu nového užívateľa
    public function register($full_name, $email, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'customer')");
        return $stmt->execute([$full_name, $email, $hash]);
    }

    // Funkcia na odhlásenie
    public function logout() {
        // Pre istotu skontrolujeme, či session beží, ak nie, spustíme ju
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = array();
        session_destroy();
    }
    // Načítanie aktuálnych údajov používateľa
    public function getUserById($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }

    // Aktualizácia profilu (meno a voliteľne aj heslo)
    public function updateProfile($user_id, $full_name, $new_password = null) {
        if (!empty($new_password)) {
            // Ak zákazník zadal nové heslo, zašifrujeme ho a uložíme
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE users SET full_name = ?, password = ? WHERE id = ?");
            return $stmt->execute([$full_name, $hash, $user_id]);
        } else {
            // Ak heslo nevyplnil, aktualizujeme iba meno
            $stmt = $this->pdo->prepare("UPDATE users SET full_name = ? WHERE id = ?");
            return $stmt->execute([$full_name, $user_id]);
        }
    }
}
?>