<?php
session_start();
require_once __DIR__ . '/BASE_DES_DONNEES/db.php';
require_once __DIR__ . '/INCLUDE/fonction.php';

if (!isset($_SESSION['Id'])) {
    header('Location: connexion.php');
    exit;
}

$pageTitle = "SAFARI-RDC+ | Mes réservations";
$pageStyles = ['CSS/reservations.css?v=1.1'];

// Vérifier si on veut réserver une destination spécifique
$destinationToBook = null;
$bookingMode = isset($_GET['id']) && !empty($_GET['id']);

if ($bookingMode) {
    $destId = (int)$_GET['id'];
    $stmt = $conn->prepare('SELECT id, nom, pays, description, prix, image FROM destinations WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $destId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $destinationToBook = $result->fetch_assoc();
        }
        $stmt->close();
    }
}

$stmt = $conn->prepare('SELECT destination, date_depart, guests, type_sejour, created_at FROM reservations WHERE user_id = ? ORDER BY created_at DESC');
$userReservations = [];
if ($stmt) {
    $stmt->bind_param('i', $_SESSION['Id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $userReservations[] = $row;
        }
    }
}
$reservationCount = count($userReservations);
$csrfToken = csrf_token();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mes réservations SAFARI-RDC+">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="CSS/header.css?v=1.5">
    <?php foreach ($pageStyles as $style): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($style) ?>">
    <?php endforeach; ?>
</head>
<body>

<?php include 'INCLUDE/header.php'; ?>

<main class="reservations">
    <section class="reservations-hero">
        <div class="reservations-shell reservations-hero-inner">
            <p class="eyebrow">Mes réservations</p>
            <h1>Vos voyages enregistrés</h1>
            <p>Retrouvez ici toutes vos réservations. Si vous n'en avez pas encore, commencez par explorer nos destinations.</p>
            <div class="reservations-hero-actions">
                <a href="destination.php" class="btn-principal">Explorer les destinations</a>
                <a href="dashboard.php" class="btn-secondary">Retour au tableau de bord</a>
            </div>
        </div>
    </section>

    <?php if ($bookingMode && $destinationToBook): ?>
    <section class="booking-form-section">
        <div class="reservations-shell">
            <div class="booking-form-card">
                <h2>Réserver : <?= htmlspecialchars($destinationToBook['nom']) ?></h2>
                <div class="destination-preview">
                    <img src="<?= htmlspecialchars($destinationToBook['image']) ?>" alt="<?= htmlspecialchars($destinationToBook['nom']) ?>">
                    <div class="destination-info">
                        <h3><?= htmlspecialchars($destinationToBook['nom']) ?></h3>
                        <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($destinationToBook['pays']) ?></p>
                        <p class="price"><?= number_format($destinationToBook['prix'], 2) ?> $ / personne</p>
                    </div>
                </div>
                <form id="bookingForm" class="booking-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="destination" value="<?= htmlspecialchars($destinationToBook['nom']) ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date">Date de départ *</label>
                            <input type="date" id="date" name="date" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label for="guests">Nombre de voyageurs *</label>
                            <input type="number" id="guests" name="guests" min="1" max="20" value="1" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="type">Type de séjour *</label>
                        <select id="type" name="type" required>
                            <option value="">Sélectionnez un type</option>
                            <option value="circuit">Circuit accompagné</option>
                            <option value="individuel">Voyage individuel</option>
                            <option value="groupe">Voyage en groupe</option>
                            <option value="lune_de_miel">Lune de miel</option>
                            <option value="famille">Voyage en famille</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes supplémentaires (optionnel)</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Précisions, demandes spéciales..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="fas fa-check-circle"></i> Confirmer la réservation
                    </button>
                    
                    <div id="formMessage" class="form-message"></div>
                </form>
            </div>
        </div>
    </section>
    
    <script>
    document.getElementById('bookingForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submitBtn');
        const formMessage = document.getElementById('formMessage');
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement en cours...';
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('reservation_submit.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            formMessage.style.display = 'block';
            
            if (result.success) {
                formMessage.className = 'form-message success';
                formMessage.innerHTML = '<i class="fas fa-check-circle"></i> ' + result.message;
                
                setTimeout(() => {
                    window.location.href = 'reservations.php';
                }, 2000);
            } else {
                formMessage.className = 'form-message error';
                formMessage.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + result.message;
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check-circle"></i> Confirmer la réservation';
            }
        } catch (error) {
            formMessage.style.display = 'block';
            formMessage.className = 'form-message error';
            formMessage.innerHTML = '<i class="fas fa-exclamation-circle"></i> Une erreur est survenue. Veuillez réessayer.';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check-circle"></i> Confirmer la réservation';
        }
    });
    </script>
    <?php endif; ?>

    <div class="reservations-shell">
        <div class="reservations-grid">
            <article class="reservations-card">
                <header class="reservations-card-header">
                    <div>
                        <h2>Réservations actuelles</h2>
                        <p><?= $reservationCount > 0 ? 'Historique complet de vos demandes de voyage.' : 'Aucune réservation pour le moment.' ?></p>
                    </div>
                    <?php if ($reservationCount > 0): ?>
                        <span class="reservations-count"><?= $reservationCount ?></span>
                    <?php endif; ?>
                </header>

                <?php if (empty($userReservations)): ?>
                    <div class="empty-state">
                        <p>Vous n'avez aucune réservation active.</p>
                        <a href="destination.php" class="btn-action">Choisir une destination</a>
                    </div>
                <?php else: ?>
                    <ul class="reservation-list">
                        <?php foreach ($userReservations as $reservation): ?>
                            <li class="reservation-item">
                                <div class="reservation-item-main">
                                    <strong><?= htmlspecialchars($reservation['destination']) ?></strong>
                                    <span><?= htmlspecialchars($reservation['type_sejour'] ?? 'Séjour') ?></span>
                                </div>
                                <dl class="reservation-item-meta">
                                    <div>
                                        <dt>Départ</dt>
                                        <dd><?= htmlspecialchars($reservation['date_depart'] ?? '—') ?></dd>
                                    </div>
                                    <div>
                                        <dt>Voyageurs</dt>
                                        <dd><?= htmlspecialchars((string) ($reservation['guests'] ?? '—')) ?></dd>
                                    </div>
                                </dl>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>

            <article class="reservations-card">
                <h2>Nouvelle réservation</h2>
                <p>Les réservations se créent depuis la page des destinations. Choisissez un voyage, puis validez votre demande.</p>
                <ul class="reservations-info-list">
                    <li><a href="destination.php">Voir les destinations disponibles</a></li>
                    <li><a href="dashboard.php">Gérer mon profil</a></li>
                    <li><a href="contact.php">Contacter le support</a></li>
                </ul>
            </article>
        </div>
    </div>
</main>

<?php include 'INCLUDE/footer.php'; ?>

</body>
</html>
