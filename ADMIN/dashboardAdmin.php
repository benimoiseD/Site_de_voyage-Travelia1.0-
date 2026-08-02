<?php
require_once __DIR__ . '/../INCLUDE/fonction.php';
require_once __DIR__ . '/../INCLUDE/db.php';
secure_session_start();

$conn = get_db_connection();

if (!isset($_SESSION['Id']) || ($_SESSION['role'] ?? 'client') !== 'admin') {
    header('Location: ../connexion.php');
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Jeton CSRF invalide.';
    } else {
        if (isset($_POST['add_destination'])) {
            $nom = sanitize_string($_POST['nom'] ?? '');
            $pays = sanitize_string($_POST['pays'] ?? '');
            $description = sanitize_string($_POST['description'] ?? '');
            $prix = (float)($_POST['prix'] ?? 0);
            $image = trim($_POST['image'] ?? '');

            if ($nom === '' || $pays === '' || $description === '') {
                $error = 'Tous les champs obligatoires doivent être renseignés.';
            } elseif (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) {
                $error = 'Le nom doit contenir entre 2 et 100 caractères.';
            } elseif (mb_strlen($pays) < 2 || mb_strlen($pays) > 100) {
                $error = 'Le pays doit contenir entre 2 et 100 caractères.';
            } elseif ($prix <= 0 || $prix > 100000) {
                $error = 'Le prix doit être compris entre 0,01 et 100000.';
            } elseif (mb_strlen($description) < 20 || mb_strlen($description) > 1000) {
                $error = 'La description doit contenir entre 20 et 1000 caractères.';
            } elseif ($image !== '' && !preg_match('/^[A-Za-z0-9_\/\.\-]+\.(jpe?g|png|webp)$/i', $image)) {
                $error = 'Le chemin de l\'image doit être valide et se terminer par .jpg, .jpeg, .png ou .webp.';
            } else {
                $stmt = $conn->prepare('INSERT INTO destinations (nom, pays, description, prix, image, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
                if ($stmt) {
                    $stmt->bind_param('sssds', $nom, $pays, $description, $prix, $image);
                    if ($stmt->execute()) {
                        $success = 'Destination ajoutée avec succès.';
                    } else {
                        $error = 'Impossible d’ajouter la destination. Vérifiez les données saisies.';
                    }
                } else {
                    $error = 'Erreur interne lors de la préparation de la création de destination.';
                }
            }
        }

        if (isset($_POST['update_destination'])) {
            $id = (int)($_POST['destination_id'] ?? 0);
            $nom = sanitize_string($_POST['nom'] ?? '');
            $pays = sanitize_string($_POST['pays'] ?? '');
            $description = sanitize_string($_POST['description'] ?? '');
            $prix = (float)($_POST['prix'] ?? 0);
            $image = trim($_POST['image'] ?? '');

            if (!validate_positive_int($id) || $nom === '' || $pays === '' || $description === '') {
                $error = 'Les informations de la destination sont incomplètes.';
            } elseif (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) {
                $error = 'Le nom doit contenir entre 2 et 100 caractères.';
            } elseif (mb_strlen($pays) < 2 || mb_strlen($pays) > 100) {
                $error = 'Le pays doit contenir entre 2 et 100 caractères.';
            } elseif ($prix <= 0 || $prix > 100000) {
                $error = 'Le prix doit être compris entre 0,01 et 100000.';
            } elseif (mb_strlen($description) < 20 || mb_strlen($description) > 1000) {
                $error = 'La description doit contenir entre 20 et 1000 caractères.';
            } elseif ($image !== '' && !preg_match('/^[A-Za-z0-9_\/\.\-]+\.(jpe?g|png|webp)$/i', $image)) {
                $error = 'Le chemin de l\'image doit être valide et se terminer par .jpg, .jpeg, .png ou .webp.';
            } else {
                $destinationCheck = $conn->prepare('SELECT id FROM destinations WHERE id = ?');
                if (!$destinationCheck) {
                    $error = 'Erreur interne lors de la vérification de la destination.';
                } else {
                    $destinationCheck->bind_param('i', $id);
                    $destinationCheck->execute();
                    $destinationCheck->store_result();
                    if ($destinationCheck->num_rows !== 1) {
                        $error = 'Destination introuvable.';
                    }
                    $destinationCheck->close();
                }
            }

            if ($error === '') {
                $stmt = $conn->prepare('UPDATE destinations SET nom = ?, pays = ?, description = ?, prix = ?, image = ? WHERE id = ?');
                if ($stmt) {
                    $stmt->bind_param('sssdsi', $nom, $pays, $description, $prix, $image, $id);
                    if ($stmt->execute()) {
                        $success = 'Destination modifiée avec succès.';
                    } else {
                        $error = 'Impossible de modifier la destination. Vérifiez les données saisies.';
                    }
                } else {
                    $error = 'Erreur interne lors de la préparation de la mise à jour de destination.';
                }
            }
        }

        if (isset($_POST['update_user_role'])) {
            $userId = (int)($_POST['user_id'] ?? 0);
            $newRole = $_POST['role'] ?? 'client';
            $newRole = in_array($newRole, ['admin', 'client'], true) ? $newRole : 'client';

            if ($userId > 0 && $userId !== ($_SESSION['Id'] ?? 0)) {
                $stmt = $conn->prepare('UPDATE users SET role = ? WHERE Id = ?');
                if ($stmt) {
                    $stmt->bind_param('si', $newRole, $userId);
                    if ($stmt->execute()) {
                        $success = 'Rôle utilisateur mis à jour.';
                    } else {
                        $error = 'Impossible de changer le rôle.';
                    }
                }
            } else {
                $error = 'Vous ne pouvez pas modifier votre propre rôle.';
            }
        }

        if (isset($_POST['delete_destination'])) {
            $id = (int)($_POST['destination_id'] ?? 0);
            if ($id > 0) {
                $stmt = $conn->prepare('DELETE FROM destinations WHERE id = ?');
                if ($stmt) {
                    $stmt->bind_param('i', $id);
                    if ($stmt->execute()) {
                        $success = 'Destination supprimée avec succès.';
                    } else {
                        $error = 'Impossible de supprimer la destination.';
                    }
                }
            }
        }

        if (isset($_POST['update_reservation_status'])) {
            $reservationId = (int)($_POST['reservation_id'] ?? 0);
            $status = $_POST['status'] ?? 'pending';
            $status = in_array($status, ['pending', 'confirmed', 'canceled'], true) ? $status : 'pending';
            if ($reservationId > 0) {
                $stmt = $conn->prepare('UPDATE reservations SET status = ? WHERE id = ?');
                if ($stmt) {
                    $stmt->bind_param('si', $status, $reservationId);
                    if ($stmt->execute()) {
                        $success = 'Statut mis à jour.';
                    } else {
                        $error = 'Impossible de mettre à jour le statut.';
                    }
                }
            }
        }
    }
}

$destinations = [];
$stmt = $conn->prepare('SELECT id, nom, pays, description, prix, image FROM destinations ORDER BY created_at DESC');
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $destinations[] = $row;
    }
}

$reservations = [];
$stmt = $conn->prepare('SELECT r.id, r.user_id, u.Nom_users, r.destination, r.date_depart, r.guests, r.type_sejour, r.status, r.created_at FROM reservations r LEFT JOIN users u ON u.Id = r.user_id ORDER BY r.created_at DESC');
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
}

$users = [];
$stmt = $conn->prepare('SELECT Id, Nom_users, Email, role, created_at FROM users ORDER BY created_at DESC');
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$stats = [
    'destinations' => count($destinations),
    'reservations' => count($reservations),
    'users' => count($users),
    'confirmed' => count(array_filter($reservations, fn($row) => ($row['status'] ?? 'pending') === 'confirmed')),
];

$pageTitle = 'SAFARI-RDC+ | Administration';
$pageStyle = '../CSS/dashboardAdmin.css';
$userInitials = dashboardInitials($_SESSION['Nom_users']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= $pageStyle ?>">
</head>


<body>
<div class="admin-page">
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <a href="../acceuil.php" class="brand">SAFARI-RDC+</a>
            <span class="admin-pill">Panneau Admin</span>
        </div>
        <nav class="admin-nav-links">
            <a href="#overview" class="active">Vue d’ensemble</a>
            <a href="#destinations">Destinations</a>
            <a href="#reservations">Réservations</a>
            <a href="#users">Utilisateurs</a>
            <a href="../logout.php">Déconnexion</a>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <div class="topbar-title">Back-office • Gestion du site</div>
            <a href="../acceuil.php" class="topbar-link">Retour au tableau de bord client</a>
        </div>

        <section class="admin-hero" id="overview">
            <p class="eyebrow">Espace sécurisé</p>
            <h1>Administration du site</h1>
            <p>Gérez les destinations, les réservations, les utilisateurs et suivez les statistiques de votre plateforme.</p>
        </section>

    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <section class="admin-grid">
        <article class="stat-card">
            <div class="icon">📍</div>
            <div>
                <h3>Destinations</h3>
                <p class="value"><?= (int)$stats['destinations'] ?></p>
            </div>
        </article>
        <article class="stat-card">
            <div class="icon">🧾</div>
            <div>
                <h3>Réservations</h3>
                <p class="value"><?= (int)$stats['reservations'] ?></p>
            </div>
        </article>
        <article class="stat-card">
            <div class="icon">👥</div>
            <div>
                <h3>Utilisateurs</h3>
                <p class="value"><?= (int)$stats['users'] ?></p>
            </div>
        </article>
        <article class="stat-card">
            <div class="icon">✅</div>
            <div>
                <h3>Confirmées</h3>
                <p class="value"><?= (int)$stats['confirmed'] ?></p>
            </div>
        </article>
    </section>

    <section class="section-card" id="destinations">
        <h2>Ajouter une destination</h2>
        <form method="POST" id="adminDestinationForm" novalidate>
            <?= csrf_input_field() ?>
            <div id="adminFormMessage" class="alert alert-info" style="display:none;" aria-live="polite"></div>
            <div class="form-grid">
                <label>Nom<input type="text" name="nom" required minlength="2" maxlength="100" pattern="[A-Za-zÀ-ÖØ-öø-ÿ0-9'\-\.\s]+" title="Nom valide, 2 à 100 caractères."></label>
                <label>Pays<input type="text" name="pays" required minlength="2" maxlength="100" pattern="[A-Za-zÀ-ÖØ-öø-ÿ0-9'\-\.\s]+" title="Pays valide, 2 à 100 caractères."></label>
                <label>Prix<input type="number" step="0.01" name="prix" value="0.00" required min="0.01" max="100000"></label>
                <label>Image<input type="text" name="image" placeholder="IMG1/B.jpg" pattern="[A-Za-z0-9_\/\.\-]+\.(jpe?g|png|webp)$" title="Chemin d'image valide se terminant par .jpg, .jpeg, .png ou .webp"></label>
            </div>
            <label style="margin-top:12px;">Description<textarea name="description" required minlength="20" maxlength="1000"></textarea></label>
            <button type="submit" name="add_destination" style="margin-top:12px;">Ajouter</button>
        </form>
    </section>

    <script>
        (function () {
            const form = document.getElementById('adminDestinationForm');
            const messageBox = document.getElementById('adminFormMessage');

            if (!form || !messageBox) {
                return;
            }

            form.addEventListener('submit', function (event) {
                const nom = form.querySelector('[name=nom]').value.trim();
                const pays = form.querySelector('[name=pays]').value.trim();
                const prix = parseFloat(form.querySelector('[name=prix]').value);
                const description = form.querySelector('[name=description]').value.trim();
                const image = form.querySelector('[name=image]').value.trim();
                const errors = [];

                if (nom.length < 2 || nom.length > 100) {
                    errors.push('Le nom doit contenir entre 2 et 100 caractères.');
                }
                if (pays.length < 2 || pays.length > 100) {
                    errors.push('Le pays doit contenir entre 2 et 100 caractères.');
                }
                if (Number.isNaN(prix) || prix <= 0 || prix > 100000) {
                    errors.push('Le prix doit être compris entre 0,01 et 100000.');
                }
                if (description.length < 20 || description.length > 1000) {
                    errors.push('La description doit contenir entre 20 et 1000 caractères.');
                }
                if (image && !/^[A-Za-z0-9_\/\.\-]+\.(jpe?g|png|webp)$/i.test(image)) {
                    errors.push('Le chemin de l\'image doit être valide et se terminer par .jpg, .jpeg, .png ou .webp.');
                }

                if (errors.length > 0) {
                    event.preventDefault();
                    messageBox.textContent = errors.join(' ');
                    messageBox.className = 'alert alert-error';
                    messageBox.style.display = 'block';
                    messageBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        })();
    </script>

    <section class="section-card">
        <h2>Destinations disponibles</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Nom</th><th>Pays</th><th>Prix</th><th>Description</th><th>Image</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($destinations as $destination): ?>
                        <tr>
                            <td>
                                <input form="edit-destination-<?= (int)$destination['id'] ?>" type="text" name="nom" value="<?= htmlspecialchars($destination['nom'], ENT_QUOTES) ?>" required minlength="2" maxlength="100">
                            </td>
                            <td>
                                <input form="edit-destination-<?= (int)$destination['id'] ?>" type="text" name="pays" value="<?= htmlspecialchars($destination['pays'], ENT_QUOTES) ?>" required minlength="2" maxlength="100">
                            </td>
                            <td>
                                <input form="edit-destination-<?= (int)$destination['id'] ?>" type="number" step="0.01" name="prix" value="<?= htmlspecialchars((string)$destination['prix'], ENT_QUOTES) ?>" required min="0.01" max="100000">
                            </td>
                            <td>
                                <textarea form="edit-destination-<?= (int)$destination['id'] ?>" name="description" required minlength="20" maxlength="1000"><?= htmlspecialchars($destination['description'], ENT_QUOTES) ?></textarea>
                            </td>
                            <td>
                                <input form="edit-destination-<?= (int)$destination['id'] ?>" type="text" name="image" value="<?= htmlspecialchars($destination['image'] ?? '', ENT_QUOTES) ?>" placeholder="IMG1/B.jpg" pattern="[A-Za-z0-9_\/\.\-]+\.(jpe?g|png|webp)$" title="Chemin d'image valide se terminant par .jpg, .jpeg, .png ou .webp">
                            </td>
                            <td>
                                <div class="actions">
                                    <form id="edit-destination-<?= (int)$destination['id'] ?>" method="POST" style="display:inline;">
                                        <?= csrf_input_field() ?>
                                        <input type="hidden" name="destination_id" value="<?= (int)$destination['id'] ?>">
                                        <button type="submit" name="update_destination" class="small">Enregistrer</button>
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <?= csrf_input_field() ?>
                                        <input type="hidden" name="destination_id" value="<?= (int)$destination['id'] ?>">
                                        <button type="submit" name="delete_destination" class="danger small">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="section-card" id="reservations">
        <div class="section-title-row">
            <h2>Réservations</h2>
            <a href="../reservations.php" class="btn-link">Voir toutes les réservations</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Client</th><th>Destination</th><th>Date</th><th>Voyageurs</th><th>Statut</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <?php $status = $reservation['status'] ?? 'pending'; ?>
                        <tr>
                            <td><?= htmlspecialchars($reservation['Nom_users'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($reservation['destination']) ?></td>
                            <td><?= htmlspecialchars($reservation['date_depart'] ?? '—') ?></td>
                            <td><?= htmlspecialchars((string)($reservation['guests'] ?? '—')) ?></td>
                            <td><span class="badge <?= htmlspecialchars($status) ?>"><?= htmlspecialchars($status === 'confirmed' ? 'Confirmée' : ($status === 'canceled' ? 'Annulée' : 'En attente')) ?></span></td>
                            <td>
                                <div class="actions">
                                    <form method="POST" style="display:inline;">
                                        <?= csrf_input_field() ?>
                                        <input type="hidden" name="reservation_id" value="<?= (int)$reservation['id'] ?>">
                                        <input type="hidden" name="status" value="confirmed">
                                        <button type="submit" name="update_reservation_status" class="small">Confirmer</button>
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <?= csrf_input_field() ?>
                                        <input type="hidden" name="reservation_id" value="<?= (int)$reservation['id'] ?>">
                                        <input type="hidden" name="status" value="canceled">
                                        <button type="submit" name="update_reservation_status" class="danger small">Annuler</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="section-card" id="users">
        <h2>Utilisateurs</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Créé le</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['Nom_users']) ?></td>
                            <td><?= htmlspecialchars($user['Email']) ?></td>
                            <td><?= htmlspecialchars($user['role'] ?? 'client') ?></td>
                            <td><?= htmlspecialchars($user['created_at'] ?? '—') ?></td>
                            <td>
                                <?php if ((int)$user['Id'] !== (int)($_SESSION['Id'] ?? 0)): ?>
                                    <form method="POST" style="display:inline;">
                                        <?= csrf_input_field() ?>
                                        <input type="hidden" name="user_id" value="<?= (int)$user['Id'] ?>">
                                        <input type="hidden" name="role" value="<?= (($user['role'] ?? 'client') === 'admin') ? 'client' : 'admin' ?>">
                                        <button type="submit" name="update_user_role" class="small">
                                            <?= (($user['role'] ?? 'client') === 'admin') ? 'Rétrograder' : 'Promouvoir' ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    </main>
</div>

<?php require_once __DIR__ . '/../INCLUDE/footer.php';?>

</body>
</html>
