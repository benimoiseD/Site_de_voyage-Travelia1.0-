<?php
require_once __DIR__ . '/INCLUDE/fonction.php';
secure_session_start();
require_once __DIR__ . '/INCLUDE/db.php';

$conn = get_db_connection();

$pageTitle = "Travelia | Détails de la destination";
$pageStyles = ['CSS/destination.css?v=1.0'];

$destination = null;
$error = '';
$reviews = [];
$averageRating = 0;
$userHasReviewed = false;
$destinationImages = [];

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

    // Récupérer les images de la destination
    if ($destination) {
        $imageStmt = $conn->prepare('
            SELECT id, image_url, alt_text, is_primary, sort_order
            FROM destination_images
            WHERE destination_id = ?
            ORDER BY is_primary DESC, sort_order ASC
        ');
        if ($imageStmt) {
            $imageStmt->bind_param('i', $id);
            $imageStmt->execute();
            $imageResult = $imageStmt->get_result();
            if ($imageResult) {
                while ($row = $imageResult->fetch_assoc()) {
                    $destinationImages[] = $row;
                }
            }
            $imageStmt->close();
        }

        // Si aucune image dans la galerie, utiliser l'image principale de la destination
        if (empty($destinationImages) && !empty($destination['image'])) {
            $destinationImages[] = [
                'id' => 0,
                'image_url' => $destination['image'],
                'alt_text' => $destination['nom'],
                'is_primary' => 1,
                'sort_order' => 0
            ];
        }
    }

    // Récupérer les avis et la moyenne
    if ($destination) {
        $reviewStmt = $conn->prepare('
            SELECT r.rating, r.comment, r.created_at, u.Nom_users
            FROM reviews r
            JOIN users u ON r.user_id = u.Id
            WHERE r.destination_id = ?
            ORDER BY r.created_at DESC
        ');
        if ($reviewStmt) {
            $reviewStmt->bind_param('i', $id);
            $reviewStmt->execute();
            $reviewResult = $reviewStmt->get_result();
            if ($reviewResult) {
                while ($row = $reviewResult->fetch_assoc()) {
                    $reviews[] = $row;
                }
            }
            $reviewStmt->close();
        }

        // Calculer la moyenne
        if (!empty($reviews)) {
            $total = 0;
            foreach ($reviews as $review) {
                $total += $review['rating'];
            }
            $averageRating = round($total / count($reviews), 1);
        }

        // Vérifier si l'utilisateur connecté a déjà laissé un avis
        if (isset($_SESSION['Id'])) {
            $checkReviewStmt = $conn->prepare('SELECT id FROM reviews WHERE user_id = ? AND destination_id = ?');
            if ($checkReviewStmt) {
                $checkReviewStmt->bind_param('ii', $_SESSION['Id'], $id);
                $checkReviewStmt->execute();
                $checkReviewStmt->store_result();
                $userHasReviewed = $checkReviewStmt->num_rows > 0;
                $checkReviewStmt->close();
            }
        }
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
    <link rel="stylesheet" href="CSS/destination.css?v=1.1">
    <link rel="stylesheet" href="CSS/header.css?v=1.0">
    <link rel="stylesheet" href="CSS/animations.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Loader Overlay -->
<div id="loaderOverlay" class="loader-overlay">
    <div class="loader"></div>
    <div class="loader-text">Envoi en cours...</div>
</div>

<?php require_once __DIR__ . '/INCLUDE/header.php'; ?>

<?php if ($destination): ?>
    <!-- Hero Section with Carousel -->
    <section class="detail-hero">
        <div class="carousel-container">
            <div class="carousel-track" id="carouselTrack">
                <?php foreach ($destinationImages as $index => $img): ?>
                    <div class="carousel-slide <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>">
                        <img src="<?= htmlspecialchars($img['image_url']) ?>" 
                             alt="<?= htmlspecialchars($img['alt_text'] ?? $destination['nom']) ?>"
                             class="carousel-image"
                             onclick="openLightbox(<?= $index ?>)">
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Carousel Controls -->
            <?php if (count($destinationImages) > 1): ?>
                <button class="carousel-btn carousel-prev" onclick="moveCarousel(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="carousel-btn carousel-next" onclick="moveCarousel(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                <!-- Carousel Indicators -->
                <div class="carousel-indicators">
                    <?php foreach ($destinationImages as $index => $img): ?>
                        <button class="carousel-indicator <?= $index === 0 ? 'active' : '' ?>" 
                                data-index="<?= $index ?>"
                                onclick="goToSlide(<?= $index ?>)"></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="hero-content">
            <h1><?= htmlspecialchars($destination['nom']) ?></h1>
            <p class="hero-country"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($destination['pays']) ?></p>
        </div>
    </section>

    <!-- Lightbox -->
    <div id="lightbox" class="lightbox">
        <button class="lightbox-close" onclick="closeLightbox()">
            <i class="fas fa-times"></i>
        </button>
        <button class="lightbox-prev" onclick="moveLightbox(-1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="lightbox-content">
            <img id="lightboxImage" src="" alt="" class="lightbox-image">
        </div>
        <button class="lightbox-next" onclick="moveLightbox(1)">
            <i class="fas fa-chevron-right"></i>
        </button>
        <div class="lightbox-counter">
            <span id="lightboxCounter">1 / 1</span>
        </div>
    </div>

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

    <!-- Section Avis -->
    <section class="reviews-section">
        <div class="container">
            <h2 class="section-title">Avis des voyageurs</h2>
            
            <!-- Moyenne des notes -->
            <div class="rating-summary">
                <div class="rating-average">
                    <div id="averageScore" class="average-score"><?= $averageRating > 0 ? $averageRating : '-' ?></div>
                    <div id="averageStars" class="average-stars">
                        <?php if ($averageRating > 0): ?>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= round($averageRating) ? 'active' : '' ?>"></i>
                            <?php endfor; ?>
                        <?php else: ?>
                            <span>Aucun avis pour le moment</span>
                        <?php endif; ?>
                    </div>
                    <div id="reviewCountText" class="review-count"><?= count($reviews) ?> avis</div>
                </div>
            </div>

            <!-- Formulaire d'avis (si connecté et n'a pas encore noté) -->
            <?php if (isset($_SESSION['Id']) && !$userHasReviewed): ?>
                <div class="review-form-container">
                    <h3>Laisser un avis</h3>
                    <form id="reviewForm" class="review-form">
                        <div class="rating-input">
                            <label>Note :</label>
                            <div class="star-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" required>
                                    <label for="star<?= $i ?>" class="star-label" data-rating="<?= $i ?>">
                                        <i class="fas fa-star"></i>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="comment-input">
                            <label for="comment">Votre commentaire :</label>
                            <textarea id="comment" name="comment" rows="4" required minlength="10" maxlength="1000" placeholder="Partagez votre expérience..."></textarea>
                        </div>
                        <?= csrf_input_field() ?>
                        <input type="hidden" name="destination_id" value="<?= $destination['id'] ?>">
                        <button type="submit" class="btn-submit-review">Envoyer mon avis</button>
                    </form>
                </div>
            <?php elseif (isset($_SESSION['Id']) && $userHasReviewed): ?>
                <div class="review-already-message">
                    <p>Vous avez déjà laissé un avis pour cette destination.</p>
                </div>
            <?php else: ?>
                <div class="review-login-message">
                    <p><a href="connexion.php">Connectez-vous</a> pour laisser un avis.</p>
                </div>
            <?php endif; ?>

            <!-- Liste des avis -->
            <div id="reviewsList" class="reviews-list">
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div class="review-author">
                                    <i class="fas fa-user-circle"></i>
                                    <span><?= htmlspecialchars($review['Nom_users']) ?></span>
                                </div>
                                <div class="review-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= $review['rating'] ? 'active' : '' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="review-content">
                                <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                            </div>
                            <div class="review-date">
                                <i class="fas fa-calendar-alt"></i>
                                <?= date('d/m/Y', strtotime($review['created_at'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-reviews">
                        <p>Soyez le premier à laisser un avis !</p>
                    </div>
                <?php endif; ?>
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

        // Gestion du formulaire d'avis
        const reviewForm = document.getElementById('reviewForm');
        const reviewCountText = document.getElementById('reviewCountText');
        const averageScore = document.getElementById('averageScore');
        const averageStars = document.getElementById('averageStars');
        const reviewsList = document.getElementById('reviewsList');

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        function renderStars(rating) {
            let html = '';
            for (let i = 1; i <= 5; i++) {
                html += `<i class="fas fa-star ${i <= rating ? 'active' : ''}"></i>`;
            }
            return html;
        }

        function renderReviewCard(review) {
            return `
                <div class="review-card">
                    <div class="review-header">
                        <div class="review-author">
                            <i class="fas fa-user-circle"></i>
                            <span>${escapeHtml(review.user_name)}</span>
                        </div>
                        <div class="review-rating">
                            ${renderStars(review.rating)}
                        </div>
                    </div>
                    <div class="review-content">
                        <p>${escapeHtml(review.comment).replace(/\n/g, '<br>')}</p>
                    </div>
                    <div class="review-date">
                        <i class="fas fa-calendar-alt"></i>
                        ${escapeHtml(review.created_at)}
                    </div>
                </div>
            `;
        }

        if (reviewForm) {
            reviewForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const formData = new FormData(form);
                const submitBtn = form.querySelector('.btn-submit-review');
                const loaderOverlay = document.getElementById('loaderOverlay');
                
                submitBtn.disabled = true;
                submitBtn.textContent = 'Envoi en cours...';
                loaderOverlay.classList.add('active');

                fetch('review_submit.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    loaderOverlay.classList.remove('active');
                    if (data.success) {
                        if (reviewCountText) {
                            reviewCountText.textContent = `${data.review_count} avis`;
                        }
                        if (averageScore) {
                            averageScore.textContent = data.average_rating > 0 ? data.average_rating : '-';
                        }
                        if (averageStars) {
                            averageStars.innerHTML = renderStars(Math.round(data.average_rating));
                        }

                        if (reviewsList) {
                            const noReviewsElement = reviewsList.querySelector('.no-reviews');
                            if (noReviewsElement) {
                                noReviewsElement.remove();
                            }
                            const card = document.createElement('div');
                            card.className = 'review-card';
                            card.innerHTML = renderReviewCard(data.review);
                            reviewsList.insertAdjacentHTML('afterbegin', card.innerHTML);
                        }

                        const formContainer = document.querySelector('.review-form-container');
                        if (formContainer) {
                            formContainer.innerHTML = '<div class="review-already-message"><p>Merci ! Votre avis a été enregistré.</p></div>';
                        }

                        alert('Votre avis a été enregistré avec succès !');
                    } else {
                        alert(data.message || 'Erreur lors de l\'envoi de l\'avis.');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Envoyer mon avis';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    loaderOverlay.classList.remove('active');
                    alert('Erreur lors de l\'envoi de l\'avis.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Envoyer mon avis';
                });
            });
        }

        // Gestion de l'affichage des étoiles
        const starLabels = document.querySelectorAll('.star-label');
        starLabels.forEach(label => {
            label.addEventListener('click', function() {
                const rating = this.dataset.rating;
                starLabels.forEach(l => {
                    const lRating = l.dataset.rating;
                    if (lRating <= rating) {
                        l.classList.add('selected');
                    } else {
                        l.classList.remove('selected');
                    }
                });
            });
        });

        // Carousel functionality
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const indicators = document.querySelectorAll('.carousel-indicator');
        const totalSlides = slides.length;

        function moveCarousel(direction) {
            currentSlide = (currentSlide + direction + totalSlides) % totalSlides;
            updateCarousel();
        }

        function goToSlide(index) {
            currentSlide = index;
            updateCarousel();
        }

        function updateCarousel() {
            slides.forEach((slide, index) => {
                slide.classList.toggle('active', index === currentSlide);
            });
            indicators.forEach((indicator, index) => {
                indicator.classList.toggle('active', index === currentSlide);
            });
        }

        // Auto-play carousel
        if (totalSlides > 1) {
            setInterval(() => moveCarousel(1), 5000);
        }

        // Lightbox functionality
        const lightbox = document.getElementById('lightbox');
        const lightboxImage = document.getElementById('lightboxImage');
        const lightboxCounter = document.getElementById('lightboxCounter');
        let lightboxIndex = 0;
        const images = <?= json_encode(array_column($destinationImages, 'image_url')) ?>;

        function openLightbox(index) {
            lightboxIndex = index;
            updateLightbox();
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.classList.remove('active');
            document.body.style.overflow = '';
        }

        function moveLightbox(direction) {
            lightboxIndex = (lightboxIndex + direction + images.length) % images.length;
            updateLightbox();
        }

        function updateLightbox() {
            lightboxImage.src = images[lightboxIndex];
            lightboxCounter.textContent = `${lightboxIndex + 1} / ${images.length}`;
        }

        // Close lightbox on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && lightbox.classList.contains('active')) {
                closeLightbox();
            }
            if (lightbox.classList.contains('active')) {
                if (e.key === 'ArrowLeft') moveLightbox(-1);
                if (e.key === 'ArrowRight') moveLightbox(1);
            }
        });

        // Close lightbox on background click
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });
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

<?php require_once __DIR__ . '/INCLUDE/footer.php'; ?>

</body>
</html>
