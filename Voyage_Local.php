<?php
require_once __DIR__ . '/INCLUDE/fonction.php';
secure_session_start();

$pageTitle = 'SAFARI-RDC+ | Voyage Local';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Découvrez les voyages locaux proposés par SAFARI-RDC+">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="CSS/header.css?v=1.2">
    <link rel="stylesheet" href="CSS/animations.css?v=1.5">
</head>
<body>
<?php require_once __DIR__ . '/INCLUDE/header.php'; ?>

<main class="section-card" style="max-width: 960px; margin: 40px auto; padding: 32px;">
    <h1>Voyage Local</h1>
    <p>Des escapades proches, accessibles et faciles à organiser pour découvrir la région autrement.</p>
    <p>Consultez les destinations disponibles pour trouver un séjour local adapté à votre budget et à votre rythme.</p>
    <p><a href="destination.php?search=RDC" class="btn-principal">Voir les destinations locales</a></p>
</main>

<?php require_once __DIR__ . '/INCLUDE/footer.php'; ?>
</body>
</html>
