<?php
require_once __DIR__ . '/BASE_DES_DONNEES/db.php';

$pageTitle = "Travelia | Destinations";
$pageStyles = ['CSS/destination.css'];

$destinations = [];
$stmt = $conn->prepare('SELECT id, nom, pays, description, prix, image FROM destinations ORDER BY created_at DESC');
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $destinations[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Découvrez nos destinations Travelia">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="CSS/destination.css?v=1.6">
    <link rel="stylesheet" href="CSS/header.css?v=1.5">
    <link rel="stylesheet" href="CSS/animations.css?v=1.5">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include 'INCLUDE/header.php'; ?>

<section class="destinations-hero">
    <div class="hero-content">
        <h1>Explorez la RDC et ses Pays limitrophes avec Travelia</h1>
        <p>Découvrez des destinations exceptionnelles et créez des souvenirs inoubliables</p>
    </div>
</section>

<section class="destinations">
    <div class="container">
        <h2 class="section-title">Nos destinations populaires</h2>
        <div class="destinations-grid">
            <?php if (empty($destinations)): ?>
                <p class="no-destinations">Aucune destination disponible pour le moment.</p>
            <?php else: ?>
                <?php foreach ($destinations as $destination): ?>
                    <div class="destination-card" data-id="<?= (int)$destination['id'] ?>">
                        <div class="card-image">
                            <img src="<?= htmlspecialchars($destination['image']) ?>" 
                                alt="<?= htmlspecialchars($destination['nom']) ?>" width="250">
                            <div class="card-overlay">
                                <a href="details_destination.php?id=<?= (int)$destination['id'] ?>" class="btn-view-details">
                                    <i class="fas fa-eye"></i> Voir les détails
                                </a>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="card-header">
                                <h3><?= htmlspecialchars($destination['nom']) ?></h3>
                            </div>
                            <div class="card-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?= htmlspecialchars($destination['pays']) ?></span>
                            </div>
                            <p class="card-description"><?= htmlspecialchars(substr($destination['description'], 0, 120)) ?>...</p>
                            <div class="card-footer">
                                <div class="price">
                                    <span class="price-label">À partir de</span>
                                    <span class="price-value"><?= htmlspecialchars(number_format($destination['prix'], 2, ',', ' ')) ?> $</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>


<?php include 'INCLUDE/footer.php'; ?>

<script src="JS/destination.js"></script>
</body>
</html>
