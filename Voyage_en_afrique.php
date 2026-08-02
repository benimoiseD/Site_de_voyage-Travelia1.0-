<?php
require_once __DIR__ . '/INCLUDE/fonction.php';
secure_session_start();

$pageTitle = 'SAFARI-RDC+ | Voyage en Afrique';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Découvrez les voyages en Afrique proposés par SAFARI-RDC+">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="CSS/header.css?v=1.2">
    <link rel="stylesheet" href="CSS/animations.css?v=1.5">
</head>
<body>
<?php require_once __DIR__ . '/INCLUDE/header.php'; ?>

<main class="section-card" style="max-width: 960px; margin: 40px auto; padding: 32px;">
    <h1>Voyage en Afrique</h1>
    <p>Explorez des circuits plus larges à travers les pays voisins et les grandes destinations africaines.</p>
    <p>Cette page vous aide à repérer les séjours les plus adaptés avant de lancer votre réservation.</p>
    <p><a href="destination.php?search=Afrique" class="btn-principal">Voir les destinations africaines</a></p>
</main>

<?php require_once __DIR__ . '/INCLUDE/footer.php'; ?>
</body>
</html>
