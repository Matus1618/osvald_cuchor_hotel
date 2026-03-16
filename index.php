<?php
session_start();
require_once 'config/db.php';
require_once 'classes/Booking.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['full_name'] : '';

// Načítanie posledných 3 recenzií pre zobrazenie
$stmt = $pdo->query("SELECT r.rating, r.comment, r.created_at, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC LIMIT 3");
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUXURY | Premium Hotel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Vynútené štýly na opravu rozbitých obrázkov a videa */
        .hero-video-wrapper { position: relative; width: 100%; height: 70vh; overflow: hidden; }
        .hero-video-wrapper video { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; max-width: 1100px; margin: 0 auto; padding: 0 20px; }
        .card { background-color: #0a0a0a; border: 1px solid #333; border-radius: 8px; overflow: hidden; }
        .card img { width: 100%; height: 250px; object-fit: cover; display: block; }
        .section-title { text-align: center; margin-bottom: 40px; color: #d4af37; font-family: 'Cinzel', serif; letter-spacing: 2px; }
    </style>
</head>
<body>

   <nav class="navbar" style="position: relative; display: flex; align-items: center; padding: 20px 0;">
        
        <button class="hamburger" id="hamburger-btn" style="margin-left: 20px; margin-right: 20px;">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <a href="index.php" class="navbar-brand" style="padding: 0; display: flex; align-items: center;">
            <img src="assets/img/logo.png" alt="LUXURY Logo" style="height: 50px; width: auto;">
        </a>

        <?php if ($isLoggedIn): ?>
            <span style="color: #d4af37; font-family: 'Cinzel', serif; letter-spacing: 1px; font-size: 14px; margin-left: auto; margin-right: 20px;">
                AHOJ, <?php echo mb_strtoupper(htmlspecialchars($userName), 'UTF-8'); ?>
            </span>
        <?php endif; ?>

        <div class="nav-links" id="nav-links">
            <a href="#galeria" class="menu-link">• Galéria</a>
            <a href="#recenzie" class="menu-link">• Hodnotenia</a>
            <a href="#kontakt" class="menu-link">• Kontakt</a>
            <?php if ($isLoggedIn): ?>
                <a href="profile.php" class="menu-link">• Môj profil</a>
                <a href="logout.php" class="menu-link">• Odhlásiť sa</a>
            <?php else: ?>
                <a href="login.php" class="menu-link">• Prihlásenie</a>
                <a href="register.php" class="menu-link">• Registrácia</a>
            <?php endif; ?>
        </div>
    </nav>

    <header class="hero-video-wrapper">
        <video autoplay muted loop playsinline>
            <source src="assets/video/hotel.mp4" type="video/mp4">
        </video>
        <div class="overlay"></div>
        <div class="hero-content">
            <h1>LUXURY HOTEL</h1>
            <p>Premium Hotel Experience</p>
        </div>
    </header>

    <?php if (isset($_SESSION['booking_error'])): ?>
        <div class="alert alert-error" style="margin-top: 20px; text-align: center;">
            <strong>Chyba:</strong> <?php echo $_SESSION['booking_error']; unset($_SESSION['booking_error']); ?>
        </div>
    <?php endif; ?>

    <?php if ($isLoggedIn): ?>
        <form action="process_booking.php" method="POST" class="booking-panel" style="margin-top: -40px; position: relative; z-index: 10; max-width: 1000px;">
            
            <div class="form-group">
                <label>Príchod</label>
                <input type="text" name="check_in" class="datepicker" placeholder="Vyberte dátum" required>
            </div>
            
            <div class="form-group">
                <label>Odchod</label>
                <input type="text" name="check_out" class="datepicker" placeholder="Vyberte dátum" required>
            </div>
            
            <div class="form-group">
                <label>Typ izby</label>
                <select name="room_type" required>
                    <option value="single">Single (1 lôžko)</option>
                    <option value="double">Double (2 lôžka)</option>
                    <option value="suite">Suite (Apartmán)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Strava</label>
                <select name="board_type" required>
                    <option value="none">Bez stravy</option>
                    <option value="half_board">Polpenzia</option>
                    <option value="full_board">Plná penzia</option>
                </select>
            </div>
            
            <div class="form-group" style="min-width: 80px;">
                <label>Zviera</label>
                <div style="padding-top: 10px;">
                    <input type="checkbox" name="has_pet" value="1" style="width: auto; margin-right: 5px;"> Áno
                </div>
            </div>

            <div class="form-group" style="flex-basis: 100%; margin-top: 10px;">
                <label>Špeciálne požiadavky (voliteľné)</label>
                <textarea name="special_requests" rows="2" placeholder="Napr. detská postieľka, neskorší príchod..."></textarea>
            </div>

            <div style="flex-basis: 100%; text-align: right; margin-top: 10px;">
                <button type="submit" class="btn-gold" style="border: none; cursor: pointer; height: 45px; padding: 0 40px;">Nájsť a rezervovať izbu</button>
            </div>
        </form>
    <?php else: ?>
        <div class="booking-panel" style="justify-content: center; margin-top: -40px; position: relative; z-index: 10;">
            <h3 style="margin: 0;">Pre vytvorenie rezervácie sa musíte prihlásiť.</h3>
        </div>
    <?php endif; ?>

    <div style="margin-top: 60px;">
        <h2 class="section-title">UBYTOVANIE</h2>
        <div class="grid">
            <div class="card">
                <img src="assets/img/7.jpg" alt="Single Room">
                <div class="card-content" style="padding: 25px 20px; text-align: center;">
                    <h3 style="color: #d4af37; margin-bottom: 15px; font-family: 'Cinzel', serif;">SINGLE</h3>
                    <p style="color: #aaa; font-size: 14px; line-height: 1.6;">Ideálna voľba pre jednotlivcov hľadajúcich komfort a súkromie s výhľadom na okolitú prírodu.</p>
                </div>
            </div>
            <div class="card">
                <img src="assets/img/2.jpg" alt="Double Room">
                <div class="card-content" style="padding: 25px 20px; text-align: center;">
                    <h3 style="color: #d4af37; margin-bottom: 15px; font-family: 'Cinzel', serif;">DOUBLE</h3>
                    <p style="color: #aaa; font-size: 14px; line-height: 1.6;">Priestranná izba s dvomi oddelenými lôžkami alebo manželskou posteľou, navrhnutá pre dokonalý oddych vo dvojici.</p>
                </div>
            </div>
            <div class="card">
                <img src="assets/img/6.jpg" alt="Suite">
                <div class="card-content" style="padding: 25px 20px; text-align: center;">
                    <h3 style="color: #d4af37; margin-bottom: 15px; font-family: 'Cinzel', serif;">SUITE</h3>
                    <p style="color: #aaa; font-size: 14px; line-height: 1.6;">Prémiový apartmán s oddelenou dennou a nočnou časťou pre maximálny luxusný zážitok.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div id="recenzie" style="margin-top: 80px; margin-bottom: 60px;">
        <h2 class="section-title">HODNOTENIA HOSTÍ</h2>
        <div class="grid">
            <?php foreach ($reviews as $review): ?>
                <div class="card" style="padding: 30px; text-align: center; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="color: #d4af37; font-size: 24px; margin-bottom: 15px;">
                            <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                        </div>
                        <p style="font-style: italic; color: #ccc; margin-bottom: 20px; line-height: 1.8;">"<?php echo htmlspecialchars($review['comment']); ?>"</p>
                    </div>
                    <div>
                        <h4 style="color: #d4af37; font-family: 'Cinzel', serif; letter-spacing: 1px; margin-bottom: 5px;"><?php echo htmlspecialchars($review['full_name']); ?></h4>
                        <span style="font-size: 12px; color: #666;"><?php echo date('d.m.Y', strtotime($review['created_at'])); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($isLoggedIn): ?>
            <div style="max-width: 600px; margin: 50px auto 0; background-color: #111; padding: 30px; border: 1px solid #333; border-radius: 8px;">
                <h3 style="color: #d4af37; font-family: 'Cinzel', serif; text-align: center; margin-bottom: 20px; letter-spacing: 1px;">ZANECHAJTE NÁM HODNOTENIE</h3>
                <form action="process_review.php" method="POST">
                    <div class="form-group" style="margin-bottom: 20px; width: 100%;">
                        <label style="color: #aaa; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 8px;">Vaša spokojnosť</label>
                        <select name="rating" required style="width: 100%; padding: 12px; background-color: #0a0a0a; border: 1px solid #444; color: #d4af37; border-radius: 4px; outline: none;">
                            <option value="5">★★★★★ - Vynikajúce</option>
                            <option value="4">★★★★☆ - Veľmi dobré</option>
                            <option value="3">★★★☆☆ - Dobré</option>
                            <option value="2">★★☆☆☆ - Priemerné</option>
                            <option value="1">★☆☆☆☆ - Zlé</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 20px; width: 100%;">
                        <label style="color: #aaa; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 8px;">Váš komentár</label>
                        <textarea name="comment" rows="4" required placeholder="Napíšte nám, ako sa vám u nás páčilo..." style="width: 100%; padding: 12px; background-color: #0a0a0a; border: 1px solid #444; color: #ffffff; border-radius: 4px; outline: none; resize: vertical;"></textarea>
                    </div>
                    <button type="submit" class="btn-gold" style="width: 100%; border: none; cursor: pointer; padding: 15px; font-size: 16px; letter-spacing: 1px;">Odoslať recenziu</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <div id="galeria" style="margin-top: 80px; margin-bottom: 60px;">
        <h2 class="section-title">GALÉRIA</h2>
        <div class="gallery-grid">
            <div class="gallery-item">
                <img src="assets/img/3.jpg" alt="Lounge a výhľad">
            </div>
            <div class="gallery-item">
                <img src="assets/img/4.jpg" alt="Exteriér hotela v zime">
            </div>
            <div class="gallery-item">
                <img src="assets/img/5.jpg" alt="Reštaurácia a dezert">
            </div>
        </div>
    </div>

    <div id="image-modal" class="modal">
        <span class="close-modal">×</span>
        <img class="modal-content" id="full-image">
    </div>

    <div id="kontakt" style="margin-top: 80px; margin-bottom: 60px;">
        <h2 class="section-title">KONTAKT</h2>
        <div class="contact-container">
            <div class="contact-info">
                <h3 style="color: #d4af37; font-family: 'Cinzel', serif; margin-bottom: 20px; letter-spacing: 1px;">KDE NÁS NÁJDETE</h3>
                <p style="margin-bottom: 15px;"><strong>Adresa:</strong> Horská cesta 1, 960 01 Zvolen</p>
                <p style="margin-bottom: 15px;"><strong>Telefón:</strong> +421 900 111 222</p>
                <p style="margin-bottom: 15px;"><strong>Email:</strong> recepcia@luxuryhotel.sk</p>
                <p style="margin-bottom: 20px; color: #d4af37;"><strong>Recepcia otvorená:</strong> 24/7</p>
                <p style="color: #aaa; font-size: 14px; line-height: 1.6;">Pre akékoľvek otázky ohľadom rezervácií, špeciálnych požiadaviek alebo organizácie podujatí nás neváhajte kontaktovať. Náš tím je vám plne k dispozícii.</p>
            </div>
            <div class="contact-map">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d85189.9678385458!2d19.043512967268846!3d48.57563584852033!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47153b3b248eb3a9%3A0x400f7d1c6978bc0!2sZvolen!5e0!3m2!1ssk!2ssk!4v1700000000000!5m2!1ssk!2ssk" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-grid">
            <div class="footer-col">
                <h4 style="color: #d4af37;">LUXURY</h4>
                <p>Zažite dokonalý luxus a oddych. Ponúkame prémiové ubytovanie, špičkovú gastronómiu a privátny wellness pre váš nezabudnuteľný pobyt.</p>
            </div>
            <div class="footer-col">
                <h4 style="color: #d4af37;">KONTAKT</h4>
                <p>📍 Horská cesta 1, 960 01 Zvolen</p>
                <p>📞 +421 900 111 222</p>
                <p>✉️ recepcia@luxuryhotel.sk</p>
            </div>
            <div class="footer-col">
                <h4 style="color: #d4af37;">RÝCHLE ODKAZY</h4>
                <a href="index.php">Domov</a>
                <?php if (!$isLoggedIn): ?>
                    <a href="login.php">Prihlásenie</a>
                    <a href="register.php">Nová registrácia</a>
                <?php else: ?>
                    <a href="profile.php">Môj profil</a>
                <?php endif; ?>
                <a href="#">Ochrana osobných údajov</a>
            </div>
        </div>
        <div class="footer-bottom">
            © <?php echo date("Y"); ?> LUXURY. Všetky práva vyhradené.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/sk.js"></script>
    <script>
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d",
            minDate: "today",
            locale: "sk"
        });
    </script>
    <script>
        // Logika pre Hamburger menu
        const hamburgerBtn = document.getElementById('hamburger-btn');
        const navLinks = document.getElementById('nav-links');

        hamburgerBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });

        // Logika pre otváranie fotiek v Galérii
        const modal = document.getElementById("image-modal");
        const modalImg = document.getElementById("full-image");
        const closeBtn = document.getElementsByClassName("close-modal")[0];
        const galleryItems = document.querySelectorAll(".gallery-item img");

        galleryItems.forEach(img => {
            img.addEventListener('click', function() {
                modal.style.display = "block";
                modalImg.src = this.src;
            });
        });

        closeBtn.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>