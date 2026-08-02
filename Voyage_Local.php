<?php
require_once __DIR__ . '/INCLUDE/fonction.php';
require_once __DIR__ . '/INCLUDE/pays.php';
require_once __DIR__ . '/INCLUDE/db.php';
secure_session_start();

if (!isset($_SESSION['Id'])) {
    header('Location: connexion.php');
    exit;
}

$conn = get_db_connection();
$pageTitle = 'SAFARI-RDC+ | Voyage Local';
$pageStyles = ['CSS/destination.css?v=1.2', 'CSS/header.css?v=1.2', 'CSS/animations.css?v=1.5'];

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

$searchTerm = trim((string)($_GET['search'] ?? ''));
$viewMode = (($_GET['view'] ?? '') === 'all') ? 'all' : 'country';
$userCountry = trim((string)($_SESSION['pays_residence'] ?? ''));

if ($userCountry === '') {
    $userCountry = get_user_country_by_id($conn, (int)$_SESSION['Id']);
    if ($userCountry !== '') {
        $_SESSION['pays_residence'] = $userCountry;
    }
}

$hasCountryFilter = $userCountry !== '' && $viewMode !== 'all';

if ($hasCountryFilter) {
    $destinations = array_values(array_filter($destinations, static function (array $destination) use ($userCountry): bool {
        return travelia_country_matches((string)($destination['pays'] ?? ''), $userCountry);
    }));
}

if ($searchTerm !== '') {
    $destinations = array_values(array_filter($destinations, static function (array $destination) use ($searchTerm): bool {
        $needle = mb_strtolower($searchTerm);
        $haystack = mb_strtolower(
            ($destination['nom'] ?? '') . ' ' .
            ($destination['pays'] ?? '') . ' ' .
            ($destination['description'] ?? '')
        );

        return mb_strpos($haystack, $needle) !== false;
    }));
}

$countryViewParams = [];
if ($searchTerm !== '') {
    $countryViewParams['search'] = $searchTerm;
}
$countryViewUrl = 'Voyage_Local.php' . ($countryViewParams ? '?' . http_build_query($countryViewParams) : '');
$allViewParams = $countryViewParams;
$allViewParams['view'] = 'all';
$allViewUrl = 'Voyage_Local.php?' . http_build_query($allViewParams);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Voyage Local Travelia">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="CSS/destination.css?v=1.2">
    <link rel="stylesheet" href="CSS/header.css?v=1.2">
    <link rel="stylesheet" href="CSS/animations.css?v=1.5">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php require_once __DIR__ . '/INCLUDE/header.php'; ?>

<section class="destinations-hero">
    <div class="hero-content">
        <h1>Voyage Local</h1>
        <p>Les destinations affichées ici correspondent d’abord à votre pays de résidence.</p>
        <?php if ($userCountry !== ''): ?>
            <p class="country-recommendation">🌍 Destinations recommandées pour votre pays : <strong><?= htmlspecialchars($userCountry) ?></strong></p>
            <div class="destinations-toolbar">
                <?php if ($viewMode === 'all'): ?>
                    <a href="<?= htmlspecialchars($countryViewUrl) ?>" class="btn-view-toggle">Retour aux destinations de mon pays</a>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($allViewUrl) ?>" class="btn-view-toggle">Voir toutes les destinations</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($searchTerm !== ''): ?>
            <p class="search-summary">Résultats pour : <strong><?= htmlspecialchars($searchTerm) ?></strong></p>
        <?php endif; ?>
    </div>
</section>

<section class="destinations">
    <div class="container">
        <h2 class="section-title"><?= $hasCountryFilter ? 'Destinations de votre pays' : 'Toutes les destinations disponibles' ?></h2>
        <div class="destinations-grid">
            <?php if (empty($destinations)): ?>
                <p class="no-destinations">
                    <?php if ($searchTerm !== '' && $hasCountryFilter): ?>
                        Aucune destination de votre pays ne correspond à votre recherche.
                    <?php elseif ($searchTerm !== ''): ?>
                        Aucune destination ne correspond à votre recherche.
                    <?php elseif ($hasCountryFilter): ?>
                        Aucune destination n’est encore disponible pour votre pays. Vous pouvez voir toutes les destinations.
                    <?php else: ?>
                        Aucune destination disponible pour le moment.
                    <?php endif; ?>
                </p>
            <?php else: ?>
                <?php foreach ($destinations as $destination): ?>
                    <div class="destination-card" data-id="<?= (int)$destination['id'] ?>">
                        <div class="card-image">
                            <img src="<?= htmlspecialchars($destination['image']) ?>" alt="<?= htmlspecialchars($destination['nom']) ?>" width="250">
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

<?php require_once __DIR__ . '/INCLUDE/footer.php'; ?>
</body>
</html>
