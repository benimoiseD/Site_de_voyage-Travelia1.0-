<?php
require_once __DIR__ . '/BASE_DES_DONNEES/db.php';

$pageTitle = "Travelia | Détails de la destination";
$pageStyles = ['CSS/destination.css?v=1.0'];

$destination = null;
$error = '';

// Récupérer l'ID depuis l'URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $conn->prepare('SELECT id, nom, pays, description, prix, image FROM destinations WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $destination = $result->fetch_assoc();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Détails de la destination Travelia">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="CSS/destination.css?v=1.0">
    <link rel="stylesheet" href="CSS/header.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include 'INCLUDE/header.php'; ?>

<?php if ($destination): ?>
    <!-- Hero Section -->
    <section class="detail-hero">
        <div class="hero-background">
            <img src="<?= htmlspecialchars($destination['image']) ?>" alt="<?= htmlspecialchars($destination['nom']) ?>">
            <div class="hero-overlay"></div>
        </div>
        <div class="hero-content">
            <h1><?= htmlspecialchars($destination['nom']) ?></h1>
            <p class="hero-country"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($destination['pays']) ?></p>
        </div>
    </section>

    <!-- À propos Section -->
    <section class="about-section">
        <div class="container">
            <div class="about-grid">
                <!-- Colonne gauche -->
                <div class="about-left">
                    <h2>À propos de cette destination</h2>
                    <div class="about-description">
                        <p><?= nl2br(htmlspecialchars($destination['description'])) ?></p>
                    </div>
                </div>

                <!-- Colonne droite -->
                <div class="about-right">
                    <div class="info-card">
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <span class="info-label">Pays</span>
                                <span class="info-value"><?= htmlspecialchars($destination['pays']) ?></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-dollar-sign"></i>
                            <div>
                                <span class="info-label">Prix de base</span>
                                <span class="info-value"><?= number_format($destination['prix'], 2) ?> $</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <span class="info-label">Durée du séjour</span>
                                <span class="info-value">3 jours / 2 nuits</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-bus"></i>
                            <div>
                                <span class="info-label">Type de voyage</span>
                                <span class="info-value">Circuit accompagné</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Informations du séjour -->
    <section class="stay-info-section">
        <div class="container">
            <h2 class="section-title">Informations du séjour</h2>
            <div class="stay-info-grid">
                <div class="stay-info-card">
                    <i class="fas fa-clock"></i>
                    <h3>Durée</h3>
                    <p>3 jours / 2 nuits</p>
                </div>
                <div class="stay-info-card">
                    <i class="fas fa-users"></i>
                    <h3>Nombre de voyageurs</h3>
                    <p>Flexible selon votre choix</p>
                </div>
                <div class="stay-info-card">
                    <i class="fas fa-bus"></i>
                    <h3>Transport</h3>
                    <p>Véhicule climatisé inclus</p>
                </div>
                <div class="stay-info-card">
                    <i class="fas fa-hotel"></i>
                    <h3>Hébergement</h3>
                    <p>Hôtel 3 étoiles</p>
                </div>
                <div class="stay-info-card">
                    <i class="fas fa-utensils"></i>
                    <h3>Repas</h3>
                    <p>Petit-déjeuner inclus</p>
                </div>
                <div class="stay-info-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Assistance</h3>
                    <p>Guide professionnel</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Calculateur de réservation -->
    <section class="calculator-section">
        <div class="container">
            <h2 class="section-title">Calculer votre réservation</h2>
            <div class="calculator-card">
                <div class="calculator-grid">
                    <div class="counter-group">
                        <label>Nombre d'adultes</label>
                        <div class="counter">
                            <button class="counter-btn" onclick="updateCounter('adults', -1)">-</button>
                            <span id="adults-count">1</span>
                            <button class="counter-btn" onclick="updateCounter('adults', 1)">+</button>
                        </div>
                    </div>
                    <div class="counter-group">
                        <label>Nombre d'enfants</label>
                        <div class="counter">
                            <button class="counter-btn" onclick="updateCounter('children', -1)">-</button>
                            <span id="children-count">0</span>
                            <button class="counter-btn" onclick="updateCounter('children', 1)">+</button>
                        </div>
                    </div>
                </div>
                <div class="price-breakdown">
                    <div class="price-row">
                        <span>Prix par adulte (100%)</span>
                        <span id="adult-price"><?= number_format($destination['prix'], 2) ?> $</span>
                    </div>
                    <div class="price-row">
                        <span>Prix par enfant (80%)</span>
                        <span id="child-price"><?= number_format($destination['prix'] * 0.8, 2) ?> $</span>
                    </div>
                    <div class="price-row total">
                        <span>Montant total</span>
                        <span id="total-price"><?= number_format($destination['prix'], 2) ?> $</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Ce qui est inclus -->
    <section class="included-section">
        <div class="container">
            <h2 class="section-title">Ce qui est inclus</h2>
            <div class="included-card">
                <div class="included-item">
                    <i class="fas fa-check"></i>
                    <span>Hébergement</span>
                </div>
                <div class="included-item">
                    <i class="fas fa-check"></i>
                    <span>Petit-déjeuner</span>
                </div>
                <div class="included-item">
                    <i class="fas fa-check"></i>
                    <span>Guide professionnel</span>
                </div>
                <div class="included-item">
                    <i class="fas fa-check"></i>
                    <span>Transport local</span>
                </div>
                <div class="included-item">
                    <i class="fas fa-check"></i>
                    <span>Entrées des sites</span>
                </div>
                <div class="included-item">
                    <i class="fas fa-check"></i>
                    <span>Assistance pendant le séjour</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Ce qui n'est pas inclus -->
    <section class="not-included-section">
        <div class="container">
            <h2 class="section-title">Ce qui n'est pas inclus</h2>
            <div class="not-included-card">
                <div class="not-included-item">
                    <i class="fas fa-times"></i>
                    <span>Billet d'avion</span>
                </div>
                <div class="not-included-item">
                    <i class="fas fa-times"></i>
                    <span>Assurance voyage</span>
                </div>
                <div class="not-included-item">
                    <i class="fas fa-times"></i>
                    <span>Dépenses personnelles</span>
                </div>
                <div class="not-included-item">
                    <i class="fas fa-times"></i>
                    <span>Achats souvenirs</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Informations importantes -->
    <section class="important-info-section">
        <div class="container">
            <h2 class="section-title">À prévoir</h2>
            <div class="important-info-card">
                <div class="important-item">
                    <i class="fas fa-passport"></i>
                    <span>Passeport valide</span>
                </div>
                <div class="important-item">
                    <i class="fas fa-shoe-prints"></i>
                    <span>Chaussures adaptées</span>
                </div>
                <div class="important-item">
                    <i class="fas fa-tshirt"></i>
                    <span>Vêtements confortables</span>
                </div>
                <div class="important-item">
                    <i class="fas fa-sun"></i>
                    <span>Protection solaire</span>
                </div>
                <div class="important-item">
                    <i class="fas fa-tint"></i>
                    <span>Eau</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Encadré prix et bouton réserver -->
    <section class="booking-section">
        <div class="container">
            <div class="booking-card">
                <div class="price-display">
                    <span class="price-label">À partir de</span>
                    <span class="price-amount"><?= number_format($destination['prix'], 2) ?> $</span>
                    <span class="price-per">par personne</span>
                </div>
                <div class="total-display">
                    <span class="total-label">Montant total :</span>
                    <span class="total-amount" id="booking-total"><?= number_format($destination['prix'], 2) ?> $</span>
                </div>
                <a href="reservations.php?id=<?= $destination['id'] ?>" class="btn-book-large">
                    Réserver maintenant
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <script>
        const basePrice = <?= $destination['prix'] ?>;
        let adults = 1;
        let children = 0;

        function updateCounter(type, change) {
            if (type === 'adults') {
                adults = Math.max(1, adults + change);
                document.getElementById('adults-count').textContent = adults;
            } else if (type === 'children') {
                children = Math.max(0, children + change);
                document.getElementById('children-count').textContent = children;
            }
            calculateTotal();
        }

        function calculateTotal() {
            const adultPrice = basePrice;
            const childPrice = basePrice * 0.8;
            const total = (adults * adultPrice) + (children * childPrice);

            document.getElementById('total-price').textContent = total.toFixed(2) + ' $';
            document.getElementById('booking-total').textContent = total.toFixed(2) + ' $';
        }
    </script>
<?php else: ?>
    <section class="error-section">
        <div class="container">
            <h2>Destination non trouvée</h2>
            <p>La destination demandée n'existe pas.</p>
            <a href="destination.php" class="btn-back">Retour aux destinations</a>
        </div>
    </section>
<?php endif; ?>

<?php include 'INCLUDE/footer.php'; ?>

</body>
</html>
