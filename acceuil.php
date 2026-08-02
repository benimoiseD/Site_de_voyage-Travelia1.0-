<?php
require_once __DIR__ . '/INCLUDE/fonction.php';
secure_session_start();

if (!isset($_SESSION["Id"])) {
    header("Location: connexion.php");
    exit;
}

require_once __DIR__ . '/INCLUDE/db.php';

$conn = get_db_connection();
$pageTitle = "Travelia | Destinations";
$pageStyles = ['CSS/destination.css?v=1.1', 'CSS/header.css?v=1.2', 'CSS/animations.css?v=1.5', 'CSS/details_destinations_fragment.css'];

$destinations = [];
$stmt = $conn->prepare('SELECT id, nom, pays, description, prix, image FROM destinations ORDER BY created_at DESC');
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $destinations[] = $row;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explorez les plus belles destinations de la RDC et des pays voisins avec SAFARI-RDC+.">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="CSS/ind.css?v=1.1">
    <link rel="stylesheet" href="CSS/destination.css?v=1.1">
    <link rel="stylesheet" href="CSS/header.css?v=1.2">
    <link rel="stylesheet" href="CSS/animations.css?v=1.5">
    <link rel="stylesheet" href="CSS/search.css?v=1.0">
    <link rel="stylesheet" href="CSS/details_destinations_fragment.css?v=1.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php
$pageTitle = "SAFARI-RDC+ | Accueil";
require_once __DIR__ . '/INCLUDE/header.php';
?>

<?php require_once __DIR__ . '/INCLUDE/search.php'; ?>



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

<section class="offers-section" aria-labelledby="offers-title">
  <div class="container">
    <h2 id="offers-title">Offres</h2>
    <p class="offers-subtitle">Des promotions, des réductions et des offres spécialement pour vous</p>

    <div class="offers-card" role="region" aria-label="Offre promotionnelle">
      <div class="offers-content">
        <small class="offers-label">Évadez-vous à moindre coût avec nos Offres Saisonnières</small>
        <h3 class="offers-headline">Il n’y a aucun piège, choisissez simplement votre escapade</h3>
        <p class="offers-text">Bénéficiez de -15 % ou plus sur certains séjours dans le monde entier. Réservez et profitez.</p>
        <a class="btn offers-cta" href="destination.php?filter=offres">Économiser avec une Offre Saisonnière</a>
      </div>

      <div class="offers-media" aria-hidden="false">
        <img src="IMG_G/IMG_PAGE/solo.jpg" alt="Plage et vacances" loading="lazy">
      </div>
    </div>
  </div>
</section>

<section class="destinations">
    <div class="container">
        <h2 class="section-title">Nos destinations populaires</h2>
        <div class="destinations-grid">
            <?php if (empty($destinations)): ?>
                <p class="no-destinations">Aucune destination disponible pour le moment.</p>
            <?php else: ?>
                <?php foreach ($destinations as $destination): ?>
                    <div class="destination-card" data-id="<?= (int)$destination['id'] ?>">
                        <div class="card-image">
                            <img src="<?= htmlspecialchars($destination['image']) ?>" 
                                alt="<?= htmlspecialchars($destination['nom']) ?>" width="250">
                            <div class="card-overlay">
                                <a href="details_destination.php?id=<?= (int)$destination['id'] ?>" class="btn-view-details">
                                    <i class="fas fa-eye"></i> Voir les détails
                                </a>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="card-header">
                                <h3><?= htmlspecialchars($destination['nom']) ?></h3>
                            </div>
                            <div class="card-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?= htmlspecialchars($destination['pays']) ?></span>
                            </div>
                            <p class="card-description"><?= htmlspecialchars(substr($destination['description'], 0, 120)) ?>...</p>
                            <div class="card-footer">
                                <div class="price">
                                    <span class="price-label">À partir de</span>
                                    <span class="price-value"><?= htmlspecialchars(number_format($destination['prix'], 2, ',', ' ')) ?> $</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
<?php require_once __DIR__ . '/INCLUDE/footer.php'; ?>

<!-- Modal container for details (loaded via AJAX) -->
<div id="detailsModal" class="modal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-content">
        <button class="close-modal" aria-label="Fermer">&times;</button>
        <div id="detailsModalBody" class="modal-body">
            <div class="modal-loading">Chargement…</div>
        </div>
    </div>
</div>

<script src="JS/destination.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const modal = document.getElementById('detailsModal');
    const modalBody = document.getElementById('detailsModalBody');
    const closeBtn = modal.querySelector('.close-modal');

    function openModal(html){
        modalBody.innerHTML = html;
        modal.classList.add('active');
        modal.setAttribute('aria-hidden','false');
        document.body.style.overflow = 'hidden';
        // manage aria-hidden / inert for background
        document.querySelectorAll('body > *:not(#detailsModal)').forEach(el => {
            try { el.setAttribute('aria-hidden', 'true'); el.inert = true; } catch(e) { el.setAttribute('aria-hidden','true'); }
        });

        // focus trap: focus first focusable element inside modal
        const focusableSelectors = 'a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, [tabindex]:not([tabindex="-1"])';
        const focusable = modal.querySelectorAll(focusableSelectors);
        const firstFocusable = focusable[0];
        const lastFocusable = focusable[focusable.length - 1];
        if(firstFocusable) firstFocusable.focus();

        // keydown handler to trap focus
        modal._trapHandler = function(e){
            if(e.key === 'Tab'){
                if(focusable.length === 0){
                    e.preventDefault();
                    return;
                }
                if(e.shiftKey){
                    if(document.activeElement === firstFocusable){
                        e.preventDefault();
                        lastFocusable.focus();
                    }
                } else {
                    if(document.activeElement === lastFocusable){
                        e.preventDefault();
                        firstFocusable.focus();
                    }
                }
            }
        };
        document.addEventListener('keydown', modal._trapHandler);
        // store previous focused element to restore later
        modal._previouslyFocused = document._previouslyFocused || document.activeElement;
    }

    function closeModal(){
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden','true');
        modalBody.innerHTML = '<div class="modal-loading">Chargement…</div>';
        document.body.style.overflow = '';
        // restore aria-hidden / inert on background
        document.querySelectorAll('body > *:not(#detailsModal)').forEach(el => {
            try { el.removeAttribute('aria-hidden'); el.inert = false; } catch(e) { el.removeAttribute('aria-hidden'); }
        });
        // remove trap
        if(modal._trapHandler){ document.removeEventListener('keydown', modal._trapHandler); delete modal._trapHandler; }
        // restore focus
        try{ if(modal._previouslyFocused) modal._previouslyFocused.focus(); } catch(e){}
    }

    // fetch fragment and open modal
    async function fetchAndOpen(id){
        try{
            const res = await fetch('details_destination_fragment.php?id=' + encodeURIComponent(id));
            if(!res.ok) throw new Error('Erreur ' + res.status);
            const html = await res.text();
            openModal(html);
        }catch(err){
            openModal('<div class="fragment-error">Impossible de charger la fiche. Réessayez.</div>');
            console.error(err);
        }
    }

    // attach to all view buttons
    document.querySelectorAll('.btn-view-details').forEach(btn => {
        btn.addEventListener('click', function(e){
            e.preventDefault();
            const card = btn.closest('.destination-card');
            const id = card?.dataset.id || btn.closest('[data-id]')?.dataset.id;
            if(id) fetchAndOpen(id);
        });
    });

    // keyboard support: Enter on card opens modal
    document.querySelectorAll('.destination-card').forEach(card => {
        card.setAttribute('tabindex', '0');
        card.addEventListener('keydown', function(e){
            if(e.key === 'Enter' || e.key === ' '){
                e.preventDefault();
                const id = card.dataset.id;
                if(id) fetchAndOpen(id);
            }
        });
    });

    // close handlers
    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function(e){ if(e.target === modal) closeModal(); });
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape' && modal.classList.contains('active')) closeModal(); });
});
</script>

</body>
</html>
