<?php
// classes/Booking.php

class Booking {
    private $pdo;

    // Konštruktor prevezme pripojenie k databáze
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Funkcia na získanie všetkých izieb
    public function getAllRooms() {
        $stmt = $this->pdo->query("SELECT * FROM rooms ORDER BY room_number ASC");
        return $stmt->fetchAll();
    }

    // Funkcia na overenie, či je izba voľná
    public function isRoomAvailable($room_id, $check_in, $check_out) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM bookings 
            WHERE room_id = ? 
            AND status != 'cancelled' 
            AND (check_in < ? AND check_out > ?)
        ");
        $stmt->execute([$room_id, $check_out, $check_in]);
        return $stmt->fetchColumn() == 0; // Vráti true, ak je izba voľná (0 konfliktov)
    }

    // Funkcia na získanie detailov o konkrétnej izbe (cena, zvieratá)
    public function getRoomDetails($room_id) {
        $stmt = $this->pdo->prepare("SELECT price_per_night, pets_allowed FROM rooms WHERE id = ?");
        $stmt->execute([$room_id]);
        return $stmt->fetch();
    }

   // Funkcia na zápis rezervácie do databázy (s požiadavkami)
    public function createBooking($user_id, $room_id, $check_in, $check_out, $board_type, $has_pet, $total_price, $special_requests = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO bookings (user_id, room_id, check_in, check_out, board_type, has_pet, total_price, status, special_requests) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)
        ");
        return $stmt->execute([$user_id, $room_id, $check_in, $check_out, $board_type, $has_pet, $total_price, $special_requests]);
    }

    // Funkcia na načítanie rezervácií konkrétneho užívateľa pre profile.php
    public function getUserBookings($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT bookings.*, rooms.room_number, rooms.type 
            FROM bookings 
            JOIN rooms ON bookings.room_id = rooms.id 
            WHERE bookings.user_id = ? 
            ORDER BY bookings.check_in DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    // Funkcia na načítanie úplne všetkých rezervácií pre administrátora
    public function getAllBookings() {
        $stmt = $this->pdo->query("
            SELECT bookings.*, users.full_name, users.email, rooms.room_number, rooms.type 
            FROM bookings 
            JOIN users ON bookings.user_id = users.id 
            JOIN rooms ON bookings.room_id = rooms.id 
            ORDER BY bookings.check_in DESC
        ");
        return $stmt->fetchAll();
    }

    // Funkcia na zmenu stavu rezervácie
    public function updateBookingStatus($booking_id, $status) {
        // Povolíme len bezpečné stavy
        $allowed_statuses = ['pending', 'confirmed', 'cancelled'];
        if (!in_array($status, $allowed_statuses)) {
            return false;
        }

        $stmt = $this->pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $booking_id]);
    }

    // Funkcia na trvalé vymazanie rezervácie z databázy
    public function deleteBooking($booking_id) {
        $stmt = $this->pdo->prepare("DELETE FROM bookings WHERE id = ?");
        return $stmt->execute([$booking_id]);
    }

    // Funkcia na pridanie novej izby do systému
    public function addRoom($room_number, $type, $beds_count, $price_per_night, $pets_allowed) {
        $stmt = $this->pdo->prepare("INSERT INTO rooms (room_number, type, beds_count, price_per_night, pets_allowed) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$room_number, $type, $beds_count, $price_per_night, $pets_allowed]);
    }

    // Funkcia na úpravu ceny konkrétnej izby
    public function updateRoomPrice($room_id, $new_price) {
        $stmt = $this->pdo->prepare("UPDATE rooms SET price_per_night = ? WHERE id = ?");
        return $stmt->execute([$new_price, $room_id]);
    }

    // Funkcia na vymazanie izby
    public function deleteRoom($room_id) {
        // Upozornenie: Ak má izba priradené historické rezervácie, databáza (cudzí kľúč) 
        // môže zabrániť jej vymazaniu pre zachovanie účtovníctva.
        $stmt = $this->pdo->prepare("DELETE FROM rooms WHERE id = ?");
        return $stmt->execute([$room_id]);
    }

    // Nájde prvú voľnú izbu zadaného typu v danom termíne
    public function findAvailableRoom($room_type, $check_in, $check_out) {
        $stmt = $this->pdo->prepare("
            SELECT id, price_per_night, pets_allowed FROM rooms 
            WHERE type = ? AND id NOT IN (
                SELECT room_id FROM bookings 
                WHERE status != 'cancelled' AND (check_in < ? AND check_out > ?)
            ) LIMIT 1
        ");
        $stmt->execute([$room_type, $check_out, $check_in]);
        return $stmt->fetch(); // Vráti údaje o izbe alebo false, ak je všetko obsadené
    }

    // Funkcia na zrušenie rezervácie zákazníkom (povolené pre čakajúce aj potvrdené)
    public function cancelBookingByUser($booking_id, $user_id) {
        $stmt = $this->pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status IN ('pending', 'confirmed')");
        return $stmt->execute([$booking_id, $user_id]);
    }
}
?>