<?php
require_once __DIR__ . '/fonction.php';
secure_session_start();

$site_title = $site_title ?? 'Travelia';
$page_title = $page_title ?? '';

$siteBase = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$siteBase = str_replace('\\', '/', $siteBase);
$siteBase = preg_replace('#/(ADMIN|include)$#i', '', $siteBase);

$is_logged_in = isset($_SESSION['Nom_users']);
$userRole = strtolower($_SESSION['role'] ?? 'client');
$is_admin = ($userRole === 'admin');
$current_page = basename($_SERVER['SCRIPT_NAME'], '.php');
$activePage = $activePage ?? $current_page;
?>

<header class="site-header">
    <div class="header-top">
        <a href="<?= $siteBase ?>/acceuil.php" class="logo" aria-label="Travelia - Accueil"><?= htmlspecialchars($site_title) ?></a>
        <div class="header-actions">
            <?php if ($is_logged_in): ?>
                <a href="<?= $siteBase ?>/Voyage_Local.php" class="btn btn-book">Voyage Local</a>
                <a href="<?= $siteBase ?>/Voyage_en_afrique.php" class="btn btn-book">Voyage en Afrique</a>
                <a href="<?= $siteBase ?>/logout.php" class="btn btn-logout">Déconnexion</a>
                <span class="welcome-message">Bienvenue, <?= htmlspecialchars($_SESSION['Nom_users']) ?>!</span>

            <?php else: ?>
                <a href="<?= $siteBase ?>/connexion.php" class="btn btn-login">Connexion</a>
                <a href="<?= $siteBase ?>/inscription.php" class="btn btn-signup">Inscription</a>
            <?php endif; ?>
        </div>
    </div>

    <nav class="header-nav" aria-label="Navigation principale Travelia">
        <ul class="nav-list">
            <?php if ($is_logged_in): ?>
                <li class="nav-item">
                    <a href="<?= $siteBase ?>/acceuil.php" class="nav-link<?= $activePage === 'acceuil' ? ' active' : '' ?>">Accueil</a>
                </li>
                <li class="nav-item">
                    <a href="<?= $siteBase ?>/destination.php" class="nav-link<?= $activePage === 'destination' ? ' active' : '' ?>">Destination</a>
                </li>
                <li class="nav-item">
                    <a href="<?= $siteBase ?>/reservations.php" class="nav-link<?= $activePage === 'reservations' ? ' active' : '' ?>">Réservations</a>
                </li>
                <li class="nav-item">
                    <a href="<?= $is_admin ? $siteBase . '/ADMIN/dashboardAdmin.php' : $siteBase . '/dashboard.php' ?>" class="nav-link<?= $activePage === 'dashboard' ? ' active' : '' ?>">Tableau de bord</a>
                </li>
                <li class="nav-item">
                    <a href="<?= $siteBase ?>/contact.php" class="nav-link<?= $activePage === 'contact' ? ' active' : '' ?>">Contact</a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a href="<?= $siteBase ?>/index.php" class="nav-link<?= $activePage === 'index' ? ' active' : '' ?>">Accueil</a>
                </li>
                <li class="nav-item">
                    <a href="<?= $siteBase ?>/destination.php" class="nav-link<?= $activePage === 'destination' ? ' active' : '' ?>">Destinations</a>
                </li>
                <li class="nav-item">
                    <a href="<?= $siteBase ?>/a pros pos.php" class="nav-link<?= $activePage === 'a pros pos' ? ' active' : '' ?>">À propos</a>
                </li>
                <li class="nav-item">
                    <a href="<?= $siteBase ?>/contact.php" class="nav-link<?= $activePage === 'contact' ? ' active' : '' ?>">Contact</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
