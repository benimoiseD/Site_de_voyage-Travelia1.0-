<?php
session_start();
require_once __DIR__ . '/Base_des_donnees/db.php';
require_once __DIR__ . '/INCLUDE/fonction.php';

$erreur = '';
$success = isset($_GET['deconnecte']) ? 'Vous avez été déconnecté avec succès.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $erreur = 'Jeton CSRF invalide.';
    } else {
        $email = trim($_POST['loginEmail'] ?? '');
        $password = $_POST['loginPassword'] ?? '';

        if (empty($email) || empty($password)) {
            $erreur = 'Veuillez renseigner l\'email et le mot de passe.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreur = 'Adresse email invalide.';
        } else {
            $stmt = $conn->prepare('SELECT Id, Nom_users, Email, MotDePasse, role, created_at FROM users WHERE Email = ?');

            if ($stmt) {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows === 1) {
                    $user = $result->fetch_assoc();

                    if (password_verify($password, $user['MotDePasse'])) {
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

                $erreur = 'Email ou mot de passe incorrect.';
            } else {
                $erreur = 'Erreur interne, veuillez réessayer.';
            }
        }
    }
}


/*$erreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["loginEmail"]);
    $password = $_POST["loginPassword"];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE Email = ?");
    $stmt->execute([$email]);
    

    if ($user && password_verify($password, $user["MotDePasse"])) {

        $_SESSION["user_id"] = $user["Id"];
        $_SESSION["username"] = $user["Nom_users"];

        header("Location: accueil.php");
        exit;

        } else ($user) {
          $erreur = "Erreur lors de l'inscription."
        };
}*/
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Connectez-vous à votre compte SAFARI-RDC+.">
    <title>SAFARI-RDC+ | Connexion</title>
    <link rel="stylesheet" href="CSS/inscription.css">
</head>

<body>
    <main class="hero">
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

            <?php if (!empty($erreur)): ?>
                <div class="auth-message error">
                    <?= htmlspecialchars($erreur) ?>
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

    <?php include 'include/footer.php'; ?>

    <script src="JS/inscription_connexion.js?v=2"></script>
</body>
</html>