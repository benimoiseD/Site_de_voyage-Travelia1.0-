<?php
require_once __DIR__ . '/INCLUDE/db.php';

$conn = get_db_connection();
$pageTitle = "Travelia | Destinations";
$pageStyles = ['CSS/destination.css?v=1.2', 'CSS/header.css?v=1.2', 'CSS/animations.css?v=1.5'];

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

$searchTerm = trim((string)($_GET['search'] ?? ''));
if ($searchTerm !== '') {
    $destinations = array_values(array_filter($destinations, static function (array $destination) use ($searchTerm): bool {
        $needle = mb_strtolower($searchTerm);
        $haystack = mb_strtolower(
            ($destination['nom'] ?? '') . ' ' .
            ($destination['pays'] ?? '') . ' ' .
            ($destination['description'] ?? '')
        );

        return mb_strpos($haystack, $needle) !== false;
    }));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Découvrez nos destinations Travelia">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="CSS/destination.css?v=1.2">
    <link rel="stylesheet" href="CSS/header.css?v=1.2">
    <link rel="stylesheet" href="CSS/animations.css?v=1.5">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php require_once __DIR__ . '/INCLUDE/header.php'; ?>

<section class="destinations-hero">
    <div class="hero-content">
        <h1>Explorez la RDC et ses Pays limitrophes avec Travelia</h1>
        <p>Découvrez des destinations exceptionnelles et créez des souvenirs inoubliables.</p>
        <?php if ($searchTerm !== ''): ?>
            <p class="search-summary">Résultats pour : <strong><?= htmlspecialchars($searchTerm) ?></strong></p>
        <?php endif; ?>
    </div>
</section>

<section class="destinations">
    <div class="container">
        <h2 class="section-title">Nos destinations populaires</h2>
        <div class="destinations-grid">
            <?php if (empty($destinations)): ?>
                <p class="no-destinations">
                    <?= $searchTerm !== '' ? 'Aucune destination ne correspond à votre recherche.' : 'Aucune destination disponible pour le moment.' ?>
                </p>
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
