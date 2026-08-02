<?php
require_once __DIR__ . '/INCLUDE/fonction.php';
require_once __DIR__ . '/INCLUDE/db.php';
secure_session_start();

$conn = get_db_connection();
$error = '';
$success = isset($_GET['deconnecte']) ? 'Vous avez été déconnecté avec succès.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Jeton CSRF invalide.';
    } else {
        $email = sanitize_string($_POST['loginEmail'] ?? '');
        $password = $_POST['loginPassword'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Veuillez renseigner l\'email et le mot de passe.';
        } elseif (!validate_email($email)) {
            $error = 'Adresse email invalide.';
        } elseif (!can_attempt_login($conn, $email)) {
            $waitTime = get_login_wait_time($conn, $email);
            $minutes = ceil($waitTime / 60);
            $error = "Trop de tentatives. Veuillez réessayer dans $minutes minute(s).";
        } else {
            $stmt = $conn->prepare('SELECT Id, Nom_users, Email, MotDePasse, role, created_at FROM users WHERE Email = ?');

            if (!$stmt) {
                $error = sql_error_message('préparer la requête de connexion');
            } else {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows === 1) {
                    $user = $result->fetch_assoc();

                    if (password_verify($password, $user['MotDePasse'])) {
                        reset_login_attempts($conn, $email);

                        // Vérifier si le hash doit être mis à jour (pour les futures mises à jour de PHP)
                        if (password_needs_rehash($user['MotDePasse'], PASSWORD_DEFAULT)) {
                            $newHash = password_hash($password, PASSWORD_DEFAULT);
                            $updateStmt = $conn->prepare('UPDATE users SET MotDePasse = ? WHERE Id = ?');
                            if ($updateStmt) {
                                $updateStmt->bind_param('si', $newHash, $user['Id']);
                                $updateStmt->execute();
                            }
                        }

                        $role = $user['role'] ?? 'client';
                        $role = in_array($role, ['admin', 'client'], true) ? $role : 'client';

                        $_SESSION['Id'] = $user['Id'];
                        $_SESSION['Nom_users'] = $user['Nom_users'];
                        $_SESSION['email'] = $user['Email'];
                        $_SESSION['role'] = $role;
                        $_SESSION['created_at'] = $user['created_at'] ?? null;

                        $redirectPage = ($role === 'admin') ? 'ADMIN/dashboardAdmin.php' : 'acceuil.php';
                        header('Location: ' . $redirectPage);
                        exit();
                    }
                }

                $remaining = record_failed_login_attempt($conn, $email);
                $error = 'Email ou mot de passe incorrect. Tentatives restantes: ' . $remaining;
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
    <meta name="description" content="Connectez-vous à votre compte SAFARI-RDC+.">
    <title>SAFARI-RDC+ | Connexion</title>
    <link rel="stylesheet" href="CSS/inscription.css">
    <link rel="stylesheet" href="CSS/animations.css?v=1.0">
</head>

<body>
    <main class="hero page-transition">
        <div class="brand">
            <h1>SAFARI-RDC+</h1>
            <p class="tag">Retrouvez vos informations et continuez la préparation de votre voyage.</p>
        </div>

        <section class="pitch">
            <h2>Espace voyageur</h2>
            <p>Connectez-vous avec l'email et le mot de passe utilisés lors de votre inscription.</p>
            <ul class="features">
                <li>Accès aux destinations</li>
                <li>Données récupérées</li>
                <li>Réservation plus simple</li>
            </ul>
        </section>

        <section class="signup">

            <?php if (!empty($success)): ?>
                <div class="auth-message success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="auth-message error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" method="POST" class="login-form auth-form active" action="connexion.php">            
            <h3>Se connecter</h3>
                <label>Email
                    <input type="email" id="loginEmail" name="loginEmail" required autocomplete="email" placeholder="vous@exemple.com">
                </label>
                <label>Mot de passe
                    <input type="password" id="loginPassword" name="loginPassword" required autocomplete="current-password" placeholder="Votre mot de passe">
                </label>
                <?= csrf_input_field() ?>
                <button type="submit" id="submitc" class="btn-primary">Se connecter</button>
                <p class="small">Pas encore de compte ? <a href="inscription.php">Créer un compte</a></p>
            </form>
        </section>
    </main>

    <?php require_once __DIR__ . '/INCLUDE/footer.php'; ?>


    <script src="JS/inscription_connexion.js?v=2"></script>
</body>
</html>