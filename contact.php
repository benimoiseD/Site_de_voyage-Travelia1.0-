<?php
require_once __DIR__ . '/INCLUDE/fonction.php';
require_once __DIR__ . '/INCLUDE/db.php';
secure_session_start();

$conn = get_db_connection();
$pageTitle = 'SAFARI-RDC+ | Contact';
$success = '';
$error = '';

$name = '';
$email = '';
$message = '';

if (isset($_SESSION['Nom_users'])) {
    $name = (string)$_SESSION['Nom_users'];
}

if (isset($_SESSION['email'])) {
    $email = (string)$_SESSION['email'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Jeton CSRF invalide.';
    } else {
        $name = trim((string)($_POST['nom'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $message = trim((string)($_POST['message'] ?? ''));

        if ($name === '' || $email === '' || $message === '') {
            $error = 'Tous les champs sont obligatoires.';
        } elseif (!validate_name($name)) {
            $error = 'Veuillez saisir un nom valide.';
        } elseif (!validate_length($name, 2, 100)) {
            $error = 'Le nom doit contenir entre 2 et 100 caractères.';
        } elseif (!validate_email($email)) {
            $error = 'Veuillez saisir une adresse email valide.';
        } elseif (!validate_length($message, 10, 2000)) {
            $error = 'Le message doit contenir entre 10 et 2000 caractères.';
        } else {
            $userId = $_SESSION['Id'] ?? null;
            $insert = $conn->prepare('INSERT INTO contact_messages (user_id, name, email, message, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())');

            if (!$insert) {
                $error = sql_error_message('enregistrer votre message');
            } else {
                $status = 'new';
                $insert->bind_param('issss', $userId, $name, $email, $message, $status);

                if ($insert->execute()) {
                    $success = 'Votre message a été envoyé avec succès. Nous vous répondrons rapidement.';
                    $name = isset($_SESSION['Nom_users']) ? (string)$_SESSION['Nom_users'] : '';
                    $email = isset($_SESSION['email']) ? (string)$_SESSION['email'] : '';
                    $message = '';
                } else {
                    $error = 'Impossible d\'enregistrer votre message pour le moment.';
                }

                $insert->close();
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
    <meta name="Description" content="Contactez-nous pour toute question ou demande d'information.">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="CSS/contact.css?v=2">
    <link rel="stylesheet" href="CSS/header.css?v=1.1">
    <link rel="stylesheet" href="CSS/animations.css?v=1.5">
</head>
<body>
<?php require_once __DIR__ . '/INCLUDE/header.php'; ?>

<main>
    <section class="contact-section">
        <h1>Contactez-nous</h1>

        <?php if ($success): ?>
            <div class="auth-message success" role="status"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="auth-message error" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form id="contactForm" class="contact-form" method="POST" action="contact.php" autocomplete="off">
            <?= csrf_input_field() ?>
            <input type="text" id="nom" name="nom" placeholder="Votre nom" value="<?= htmlspecialchars($name) ?>" required>
            <input type="email" id="email" name="email" placeholder="Votre email" value="<?= htmlspecialchars($email) ?>" autocomplete="email" required>
            <textarea id="message" name="message" placeholder="Votre message" rows="5" required><?= htmlspecialchars($message) ?></textarea>
            <input type="submit" value="Envoyer">
        </form>
        <div id="formMessage"></div>
    </section>
</main>

<script src="JS/contact.js"></script>

<?php require_once __DIR__ . '/INCLUDE/footer.php'; ?>
</body>
</html>
