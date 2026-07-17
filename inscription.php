<?php
require_once __DIR__ . '/INCLUDE/fonction.php';
secure_session_start();
require_once __DIR__ . '/BASE_DES_DONNEES/db.php';

$erreur = '';
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $erreur = 'Jeton CSRF invalide.';
    } else {
        $name = sanitize_string($_POST['name'] ?? '');
        $email = sanitize_string($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            $erreur = 'Tous les champs sont obligatoires.';
        } elseif (!validate_name($name)) {
            $erreur = 'Le nom contient des caractères invalides.';
        } elseif (!validate_length($name, 2, 100)) {
            $erreur = 'Le nom doit contenir entre 2 et 100 caractères.';
        } elseif (!validate_email($email)) {
            $erreur = 'Adresse email invalide.';
        } elseif (!validate_password($password)) {
            $erreur = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.';
        } else {
            $stmt = $conn->prepare('SELECT Id FROM users WHERE Email = ?');

            if ($stmt) {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $erreur = 'Impossible de créer le compte. Veuillez vérifier vos informations.';
                } else {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $role = 'client';
                    $insert = $conn->prepare('INSERT INTO users (Nom_users, Email, MotDePasse, role) VALUES (?, ?, ?, ?)');

                    if ($insert) {
                        $insert->bind_param('ssss', $name, $email, $passwordHash, $role);

                        if ($insert->execute()) {
                            $_SESSION['Id'] = $conn->insert_id;
                            $_SESSION['Nom_users'] = $name;
                            $_SESSION['email'] = $email;
                            $_SESSION['role'] = $role;
                            $_SESSION['created_at'] = null;
                            header('Location: acceuil.php');
                            exit();
                        }

                        $erreur = 'Erreur lors de l\'inscription, veuillez réessayer.';
                    } else {
                        $erreur = 'Erreur interne, veuillez réessayer.';
                    }
                }
            } else {
                $erreur = 'Erreur interne, veuillez réessayer.';
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
    <meta name="description" content="Découvrez les meilleures destinations touristiques avec SAFARI-RDC+">
    <title>SAFARI-RDC+</title>
    <link rel="stylesheet" href="CSS/inscription.css">
    <link rel="stylesheet" href="CSS/animations.css?v=1.0">
</head>

<body>
    <main class="hero page-transition">
        <div class="brand">
            <h1>SAFARI-RDC+</h1>
            <p class="tag">Explorez des voyages authentiques hors des sentiers battus.</p>
        </div>

        <section class="pitch">
            <h2>Voyages d'aventure, détente et culture</h2>
            <p>Itinéraires sur mesure, guides locaux, expériences responsables — rejoignez notre communauté pour accéder aux offres complètes.</p>
            <ul class="features">
                <li>Cartes interactives et itinéraires</li>
                <li>Offres personnalisées</li>
                <li>Avis et assistance locale</li>
            </ul>
        </section>

        <section class="signup">
            <div class="auth-toggle">
                <button type="button" class="toggle-btn active" data-mode="signup">Inscription</button>
            </div>
            
            <?php if (!empty($erreur)): ?>
                <div class="auth-message error">
                    <?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>

            <!-- Formulaire d'inscription -->
            <form id="signupForm" method="POST" action="inscription.php" class="signup-form auth-form active" >
                <h3>Créer un compte</h3>
                <label>Nom complet
                    <input type="text" id="name" name="name" required minlength="2" autocomplete="name" placeholder="Votre nom" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </label>
                <label>Email
                    <input type="email" id="email" name="email" required autocomplete="email" placeholder="vous@exemple.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </label>
                <label>Mot de passe
                    <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Choisissez un mot de passe (8+ caractères, majuscule, minuscule, chiffre)">
                </label>
                <?= csrf_input_field() ?>
                <button type="submit" name="submit" id="submiti" class="btn-primary">S'inscrire</button>
                <p class="small">Vous avez deja un compte ? <a href="connexion.php">Connectez-vous</a></p>
            </form>
        </section>
    </main>
    
    <script src="JS/inscription_connexion.js?v=2"></script>

    <?php include 'include/footer.php'; ?>


</body>
</html>