<?php
require_once __DIR__ . '/INCLUDE/fonction.php';
secure_session_start();
require_once __DIR__ . '/BASE_DES_DONNEES/db.php';

if (!isset($_SESSION['Id'])) {
    header('Location: connexion.php');
    exit;
}

$success = '';
$error = '';
$name = $_SESSION['Nom_users'];
$email = $_SESSION['email'] ?? '';
$userRole = $_SESSION['role'] ?? 'client';
$userRole = in_array($userRole, ['admin', 'client'], true) ? $userRole : 'client';
$isAdmin = ($userRole === 'admin');
$createdAt = $_SESSION['created_at'] ?? null;

$avatarsFile = __DIR__ . '/BASE_DES_DONNEES/data/avatars.json';

$avatars = [];
if (file_exists($avatarsFile)) {
    $avatars = json_decode(file_get_contents($avatarsFile), true) ?: [];
}

$userReservations = [];
$stmt = $conn->prepare('SELECT destination, date_depart, guests, type_sejour, created_at FROM reservations WHERE user_id = ? ORDER BY created_at DESC');
if ($stmt) {
    $stmt->bind_param('i', $_SESSION['Id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $userReservations[] = $row;
        }
    }
}
$reservationCount = count($userReservations);
$recentReservations = array_slice($userReservations, 0, 3);

$avatarPath = $avatars[$_SESSION['Id']] ?? '';
if ($avatarPath && !file_exists(__DIR__ . '/' . $avatarPath)) {
    $avatarPath = '';
}

function dashboardInitials(string $fullName): string
{
    $parts = preg_split('/\s+/', trim($fullName));
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= mb_strtoupper(mb_substr($part, 0, 1));
    }
    return $initials ?: '?';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Jeton CSRF invalide.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

    if ($name === '') {
        $error = 'Le nom complet est obligatoire.';
    } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Veuillez saisir une adresse email valide.';
    }

    if (empty($error)) {
        $stmt = $conn->prepare('SELECT Id FROM users WHERE Email = ? AND Id <> ?');
        if ($stmt) {
            $stmt->bind_param('si', $email, $_SESSION['Id']);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = 'Cet email est déjà utilisé par un autre compte.';
            } else {
                $update = $conn->prepare('UPDATE users SET Nom_users = ?, Email = ? WHERE Id = ?');
                $update->bind_param('ssi', $name, $email, $_SESSION['Id']);

                if ($update && $update->execute()) {
                    $_SESSION['Nom_users'] = $name;
                    $_SESSION['email'] = $email;
                    $success = 'Votre profil a été mis à jour avec succès.';
                } else {
                    $error = 'Erreur lors de la mise à jour. Veuillez réessayer.';
                }
            }
        } else {
            $error = 'Erreur interne, veuillez réessayer plus tard.';
        }
    }

    if (empty($error) && isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $avatarFile = $_FILES['avatar'];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $avatarFile['tmp_name']);
        finfo_close($fileInfo);

        if (!isset($allowedTypes[$mimeType])) {
            $error = 'Format de photo invalide. Utilisez JPEG, PNG ou WebP.';
        } elseif ($avatarFile['size'] > 2 * 1024 * 1024) {
            $error = 'La photo doit faire moins de 2 Mo.';
        } else {
            $extension = $allowedTypes[$mimeType];
            $uploadDir = __DIR__ . '/uploads/avatars';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $targetName = 'avatar_' . $_SESSION['Id'] . '.' . $extension;
            $targetPath = $uploadDir . '/' . $targetName;

            if (move_uploaded_file($avatarFile['tmp_name'], $targetPath)) {
                $avatarPath = 'uploads/avatars/' . $targetName;
                $avatars[$_SESSION['Id']] = $avatarPath;
                file_put_contents($avatarsFile, json_encode($avatars, JSON_PRETTY_PRINT));
                $_SESSION['avatar'] = $avatarPath;
                $success = 'Profil mis à jour et photo envoyée avec succès.';
            } else {
                $error = 'Impossible de téléverser la photo. Veuillez réessayer.';
            }
        }
    }
}
}

$pageTitle = "SAFARI-RDC+ | Tableau de bord";
$pageStyles = ['CSS/dashboard.css?v=5'];
$userInitials = dashboardInitials($_SESSION['Nom_users']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Tableau de bord voyageur SAFARI-RDC+">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="CSS/header.css?v=1.5">
    <link rel="stylesheet" href="CSS/animations.css?v=1.5">
    <?php foreach ($pageStyles as $style): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($style) ?>">
    <?php endforeach; ?>
</head>
<body>

<?php include 'INCLUDE/header.php'; ?>

<main class="dashboard">
    <header class="dashboard-hero">
        <div class="dashboard-shell dashboard-hero-inner">
            <div class="dashboard-hero-profile">
                <?php if ($avatarPath): ?>
                    <img src="<?= htmlspecialchars($avatarPath) ?>" alt="Photo de profil" class="avatar-preview">
                <?php else: ?>
                    <span class="avatar-preview avatar-preview--initials" aria-hidden="true"><?= htmlspecialchars($userInitials) ?></span>
                <?php endif; ?>
                <div class="dashboard-hero-text">
                    <p class="eyebrow"><?= $isAdmin ? 'Administration' : 'Tableau de bord' ?></p>
                    <h1>Bienvenue, <?= htmlspecialchars($_SESSION['Nom_users']) ?></h1>
                    <?php if (!empty($_SESSION['email'])): ?>
                        <p class="dashboard-hero-email"><?= htmlspecialchars($_SESSION['email']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($createdAt)): ?>
                        <p class="dashboard-hero-email">Compte créé le <?= htmlspecialchars(date('d/m/Y', strtotime($createdAt))) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <a href="destination.php" class="btn-principal">Explorer les destinations</a>
        </div>
    </header>

    <div class="dashboard-shell dashboard-content">
        <section class="dashboard-stats" aria-label="Résumé du compte">
            <article class="stat-card stat-card--green">
                <p class="stat-label">Réservations</p>
                <p class="stat-value"><?= $reservationCount ?></p>
                <p class="stat-detail">
                    <?= $reservationCount > 0 ? 'Voyage(s) enregistré(s)' : 'Aucune pour l\'instant' ?>
                </p>
            </article>
            <article class="stat-card stat-card--orange">
                <p class="stat-label">Compte</p>
                <p class="stat-value"><?= $isAdmin ? 'Admin' : 'Actif' ?></p>
                <p class="stat-detail"><?= $isAdmin ? 'Accès administration activé' : 'Prêt à réserver' ?></p>
            </article>
            <article class="stat-card stat-card--light">
                <p class="stat-label">Support</p>
                <p class="stat-value">24/7</p>
                <p class="stat-detail"><a href="contact.php">Nous contacter</a></p>
            </article>
        </section>

        <div class="dashboard-layout">
            <aside class="dashboard-sidebar" aria-label="Navigation du compte">
                <nav class="dashboard-card dashboard-nav">
                    <h2>Mon espace</h2>
                    <ul class="dashboard-quick-links">
                        <li><a href="destination.php">Destinations</a></li>
                        <li><a href="reservations.php">Mes réservations <span class="badge"><?= $reservationCount ?></span></a></li>
                        <li><a href="contact.php">Support</a></li>
                        <li><a href="logout.php" class="link-muted">Déconnexion</a></li>
                    </ul>
                </nav>

                <article class="dashboard-card dashboard-info">
                    <h2>Informations</h2>
                    <dl class="profile-meta">
                        <div class="profile-meta-row">
                            <dt>Nom</dt>
                            <dd><?= htmlspecialchars($_SESSION['Nom_users']) ?></dd>
                        </div>
                        <?php if (!empty($_SESSION['email'])): ?>
                            <div class="profile-meta-row">
                                <dt>Email</dt>
                                <dd><?= htmlspecialchars($_SESSION['email']) ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                    <p class="dashboard-info-note">Ces informations sont utilisées pour vos réservations et la communication avec notre équipe.</p>
                </article>
            </aside>

            <div class="dashboard-main">
                <?php if ($isAdmin): ?>
                    <article class="dashboard-card">
                        <header class="card-header">
                            <h2>Accès administration</h2>
                            <p>Votre compte est configuré comme administrateur.</p>
                        </header>
                        <p class="dashboard-info-note">Vous pouvez gérer votre profil et consulter les réservations depuis cet espace.</p>
                    </article>
                <?php endif; ?>

                <article class="dashboard-card">
                    <header class="card-header">
                        <h2>Modifier mon profil</h2>
                        <p>Mettez à jour vos coordonnées et votre photo de profil.</p>
                    </header>

                    <?php if ($success): ?>
                        <div class="alert alert-success" role="status"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-error" role="alert"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" class="profile-form" enctype="multipart/form-data">
                        <?= csrf_input_field() ?>
                        <div class="form-row">
                            <label for="profile-name">Nom complet</label>
                            <input type="text" id="profile-name" name="name" value="<?= htmlspecialchars($name) ?>" required>
                        </div>
                        <div class="form-row">
                            <label for="profile-email">Adresse email</label>
                            <input type="email" id="profile-email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                        </div>
                        <div class="form-row">
                            <label for="profile-avatar">Photo de profil</label>
                            <input type="file" id="profile-avatar" name="avatar" accept="image/jpeg,image/png,image/webp">
                            <span class="form-hint">JPEG, PNG ou WebP — 2 Mo maximum.</span>
                        </div>
                        <button type="submit" name="update_profile" class="btn-primary">Enregistrer les modifications</button>
                    </form>
                </article>

                <article class="dashboard-card">
                    <header class="card-header card-header--inline">
                        <div>
                            <h2>Dernières réservations</h2>
                            <p>Vos <?= min(3, $reservationCount) ?> dernière(s) demande(s) de voyage.</p>
                        </div>
                        <?php if ($reservationCount > 0): ?>
                            <a href="reservations.php" class="link-action">Tout voir</a>
                        <?php endif; ?>
                    </header>

                    <?php if (empty($recentReservations)): ?>
                        <div class="empty-state">
                            <p>Vous n'avez pas encore de réservation.</p>
                            <a href="destination.php" class="btn-secondary">Choisir une destination</a>
                        </div>
                    <?php else: ?>
                        <ul class="reservation-list">
                            <?php foreach ($recentReservations as $reservation): ?>
                                <li class="reservation-item">
                                    <div class="reservation-item-main">
                                        <strong><?= htmlspecialchars($reservation['destination']) ?></strong>
                                        <span><?= htmlspecialchars($reservation['type_sejour'] ?? 'Séjour') ?></span>
                                    </div>
                                    <dl class="reservation-item-meta">
                                        <div>
                                            <dt>Départ</dt>
                                            <dd><?= htmlspecialchars($reservation['date_depart'] ?? '—') ?></dd>
                                        </div>
                                        <div>
                                            <dt>Voyageurs</dt>
                                            <dd><?= htmlspecialchars((string) ($reservation['guests'] ?? '—')) ?></dd>
                                        </div>
                                    </dl>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </article>
            </div>
        </div>
    </div>
</main>

<?php include 'INCLUDE/footer.php'; ?>


</body>
</html>
