<?php
require_once __DIR__ . '/INCLUDE/fonction.php';
secure_session_start();

$pageTitle = 'SAFARI-RDC+ | Politique de confidentialité';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="CSS/header.css?v=1.2">
    <link rel="stylesheet" href="CSS/animations.css?v=1.5">
</head>
<body>
<?php require_once __DIR__ . '/INCLUDE/header.php'; ?>
<main class="section-card" style="max-width: 960px; margin: 40px auto; padding: 32px;">
    <h1>Politique de confidentialité</h1>
    <p>Cette page résume la manière dont les données personnelles sont traitées sur la plateforme.</p>
</main>
<?php require_once __DIR__ . '/INCLUDE/footer.php'; ?>
</body>
</html>
