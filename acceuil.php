<?php
require_once __DIR__ . '/INCLUDE/fonction.php';
secure_session_start();

if (!isset($_SESSION["Id"])) {
    header("Location: connexion.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explorez les plus belles destinations de la RDC et des pays voisins avec SAFARI-RDC+.">
    <title>SAFARI-RDC+ | Accueil</title>
    <link rel="stylesheet" href="CSS/ind.css?v=1.0">
</head>
<body>

<?php
$pageTitle = "SAFARI-RDC+ | Accueil";
include 'include/header.php';
?>


<section class="pt-principal">
    <div class="hero-contenu">
        <p class="accroche">Voyages, safaris et découvertes</p>
        <h1>Bienvenue à SAFARI-RDC+</h1>
        <h2>Découvrez la RDC et les merveilles des pays voisins.</h2>
        <div class="hero-actions">
            <a href="destination.php" class="btn-principal">Voir les destinations</a>
        </div>
    </div>
</section>

<section class="pt-principal2">
    <h1>Nos catégories de voyage</h1>
    <p class="intro-section">Choisissez le type d'expérience qui vous ressemble et laissez-nous vous guider vers des paysages inoubliables.</p>
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
    <h1>Pourquoi voyager avec nous ?</h1>
    <div class="avantages-list">
        <article>
            <span>01</span>
            <h2>Destinations sélectionnées</h2>
            <p>Nous mettons en avant des lieux remarquables en RDC, en Tanzanie, au Rwanda, en Zambie, en Angola et dans d'autres pays voisins.</p>
        </article>
        <article>
            <span>02</span>
            <h2>Expériences variées</h2>
            <p>Safari, détente au bord du lac, randonnées, chutes d'eau, parcs naturels et séjours culturels selon votre rythme.</p>
        </article>
        <article>
            <span>03</span>
            <h2>Accompagnement simple</h2>
            <p>Consultez les destinations, inscrivez-vous, puis préparez votre voyage avec une équipe disponible et proche de vous.</p>
        </article>
        <article>
            <span>04</span>
            <h2>Offres et promotions</h2>
            <p>Profitez de nos offres exclusives et de nos promotions pour voyager à prix avantageux.</p>
        </article>
        <article>
            <span>05</span>
            <h2>Satisfaction garantie</h2>
            <p>Nous nous engageons à vous offrir une expérience de voyage exceptionnelle, avec un service client attentif et réactif.</p>
        </article>
        <article>
            <span>06</span>
            <h2>Support 24/7</h2>
            <p>Avec nous, votre satisfaction est notre priorité. Notre équipe est disponible 24 heures sur 24, 7 jours sur 7, pour vous accompagner tout au long de votre voyage.</p>
        </article>
    </div>
</section>

<section class="appel-action">
    <h1>Prêt à préparer votre prochaine aventure ?</h1>
    <p>Commencez par explorer nos destinations phares et trouvez le voyage qui vous inspire.</p>
    <a href="destination.php" class="btn-principal">Explorer maintenant</a>
</section>


<script src="JS/acceuil.js"></script>
<?php include 'include/footer.php'; ?>

</body>
</html>
