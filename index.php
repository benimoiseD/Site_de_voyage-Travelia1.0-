

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SAFARI-RDC+ vous aide à découvrir des voyages, safaris et destinations autour de la RDC avant de créer votre compte.">
    <title>SAFARI-RDC+ | Bienvenue</title>
    <link rel="stylesheet" href="CSS/ind.css">
</head>
<body>

<?php
$pageTitle = "SAFARI-RDC+ | Accueil";
include 'include/header.php';
?>
<?php if(isset($_GET['logout'])): ?>

<div class="logout-success">

    ✅ Vous avez été déconnecté avec succès.
    À bientôt sur Travelia !

</div>

<?php endif; ?>

<section class="pt-principal">
    <div class="hero-contenu">
        <p class="accroche">Votre aventure commence ici</p>
        <h1>SAFARI-RDC+</h1>
        <h2>Découvrez des destinations uniques en RDC et dans les pays voisins avant de créer votre compte.</h2>
        <div class="hero-actions">
            <a href="inscription.php" class="btn-principal">Créer un compte</a>
            <a href="connexion.php" class="btn-secondaire">Se connecter</a>
        </div>
    </div>
</section>

<section class="presentation">
    <div class="presentation-texte">
        <p class="sur-titre">Avant de vous connecter</p>
        <h1>Une plateforme simple pour préparer votre prochain voyage</h1>
        <p>
            SAFARI-RDC+ rassemble des idées de séjours, des destinations inspirantes et des expériences adaptées aux voyageurs seuls, aux couples et aux familles.
            Connectez-vous pour accéder aux détails, organiser vos choix et commencer votre réservation.
        </p>
    </div>
    <div class="presentation-image">
            <img src="IMG_G/IMG_PAGE/index.jpg" alt="Voyageur explorant seul">
    </div>
</section>

<section class="pt-principal2">
    <h1>Aperçu des expériences</h1>
    <p class="intro-section">Voici quelques types de voyages que vous pourrez explorer plus en détail après votre inscription ou votre connexion.</p>
    <div class="generale"> 

        <article class="img">
            <h1>Voyage en Solo</h1>
            <img src="IMG_G/IMG_PAGE/solo.jpg" alt="Voyageur explorant seul">
            <p>
                Partez librement à l-a découverte des perles naturelles, des cultures locales et des grands espaces.
            </p>
        </article>

        <article class="img">
            <h1>Voyage en Couple</h1>
            <img src="IMG_G/IMG_PAGE/couple.jpg" alt="Voyage en couple">
            <p>
                Vivez une escapade douce et mémorable entre lacs, montagnes, réserves et villes animées.
            </p>
        </article>

        <article class="img">
            <h1>Voyage en Famille</h1>
            <img src="IMG_G/IMG_PAGE/famille.jpg" alt="Voyage en famille">
            <p>
                Offrez à toute la famille une aventure sûre, enrichissante et adaptée à chaque âge.
            </p>
        </article>

        <article class="img">
            <h1>Voyage en Groupe</h1>
            <img src="IMG_G/IMG_PAGE/groupe.jpg" alt="Voyage en groupe">
            <p>
                Explorez ensemble des destinations uniques, avec des activités et des hébergements adaptés à tous.
            </p>
        </article>

        <article class="img">
            <h1>Lune de Miel</h1>
            <img src="IMG_G/IMG_PAGE/lune_de_miel.jpg" alt="Voyage d'aventure">
            <p>
                Plongez dans des expériences palpitantes, des safaris aux randonnées, pour les amateurs de sensations fortes.
            </p>
        </article>

        <article class="img">
            <h1>Voyage d'Aventure</h1>
            <img src="IMG_G/IMG_PAGE/aventure.jpg" alt="Voyage d'aventure">
            <p>
                Plongez dans des expériences palpitantes, des safaris aux randonnées, pour les amateurs de sensations fortes.
            </p>
        </article>

    </div>
</section>

<section class="avantages">
    <h1>Ce que vous trouverez après connexion</h1>
    <div class="avantages-list">
        <article>
            <span>01</span>
            <h2>Destinations détaillées</h2>
            <p>Consultez des lieux remarquables en RDC, au Rwanda, en Tanzanie, en Zambie, en Angola et dans d'autres pays voisins.</p>
        </article>
        <article>
            <span>02</span>
            <h2>Préparation du voyage</h2>
            <p>Comparez les expériences, choisissez votre type de séjour et gardez vos idées de voyage au même endroit.</p>
        </article>
        <article>
            <span>03</span>
            <h2>Contact plus direct</h2>
            <p>Une fois connecté, vous pouvez avancer plus facilement vers une demande d'information ou une réservation.</p>
        </article>
    </div>
</section>

<section class="appel-action">
    <h1>Prêt à entrer dans l'espace voyageur ?</h1>
    <p>Créez votre compte pour accéder aux destinations et commencer à préparer votre aventure.</p>
    <div class="hero-actions">
        <a href="inscription.php" class="btn-principal">S'inscrire</a>
        <a href="connexion.php" class="btn-secondaire">J'ai déjà un compte</a>
    </div>
</section>

<?php include 'include/footer.php'; ?>

<script src="JS/index.js"></script>

</body>
</html>
