<?php



if (session_status() === PHP_SESSION_NONE) {

    session_start();

}



/**

 * BASE URL (plus stable après réorganisation)

 */

$siteBase = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

$siteBase = str_replace('\\', '/', $siteBase);

$siteBase = preg_replace('#/(ADMIN|include)$#i', '', $siteBase);



/**

 * SESSION(Gestion utilisateur)

 */

$isLoggedIn = isset($_SESSION['Nom_users']);



$userRole = strtolower($_SESSION['role'] ?? 'client');

$isAdmin = ($userRole === 'admin');

?>



<header>

    <nav>

        <span class="logo">SAFARI-RDC+</span>



        <ul class="list">



            <!--  NON CONNECTÉ : INDEX -->

            <?php if (!$isLoggedIn): ?>



                <li><a href="<?= $siteBase ?>/index.php">Accueil</a></li>

                <li><a href="<?= $siteBase ?>/a pros pos.php">À propos</a></li>

                <li><a href="<?= $siteBase ?>/contact.php">Contact</a></li>



                <li><a href="<?= $siteBase ?>/connexion.php">Connexion</a></li>

                <li><a href="<?= $siteBase ?>/inscription.php" class="nav-cta">Inscription</a></li>



            <?php else: ?>



                <!--  CONNECTÉ : ACCUEIL DASHBOARD USER -->

                <li><a href="<?= $siteBase ?>/acceuil.php">Accueil</a></li>



                <li><a href="<?= $siteBase ?>/a pros pos.php">À propos</a></li>

                <li><a href="<?= $siteBase ?>/contact.php">Contact</a></li>



                <li><a href="<?= $siteBase ?>/destination.php">Destinations</a></li>



                <li><a href="<?= $siteBase ?>/reservations.php">Réservations</a></li>



                <!-- ADMIN / CLIENT -->

                <li>

                    <a href="<?= $isAdmin

                        ? $siteBase . '/ADMIN/dashboardAdmin.php'

                        : $siteBase . '/dashboard.php' ?>">

                        Tableau de bord

                    </a>

                </li>

                <li><a href="#" id="logoutBtn" class="nav-cta">Déconnexion</a></li>


                <?php if ($isLoggedIn): ?>

                <div id="logoutModal" class="logout-modal">

                    <div class="logout-box">

                        <div class="logout-icon">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>

                        <h2>Déconnexion</h2>

                        <p>
                            Êtes-vous sûr de vouloir vous déconnecter de votre compte Travelia ?
                        </p>

                        <div class="logout-buttons">

                            <button id="cancelLogout">
                                Annuler
                            </button>

                            <a href="<?= $siteBase ?>/logout.php" id="confirmLogout">
                                Se déconnecter
                            </a>

                        </div>

                    </div>

                </div>

                <link rel="stylesheet" href="<?= $siteBase ?>/CSS/logout.css">

                <script src="<?= $siteBase ?>/JS/logout.js"></script>

            <?php endif; ?>
        <?php endif; ?>



        </ul>

    </nav>

</header>