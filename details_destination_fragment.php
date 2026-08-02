<?php
require_once __DIR__ . '/INCLUDE/db.php';
require_once __DIR__ . '/INCLUDE/fonction.php';
secure_session_start();

header('Content-Type: text/html; charset=utf-8');

$conn = get_db_connection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo '<div class="fragment-error">ID manquant ou invalide.</div>';
    exit;
}

$destination = null;
$destinationImages = [];
$reviews = [];
$averageRating = 0;

$stmt = $conn->prepare('SELECT id, nom, pays, description, prix, image FROM destinations WHERE id = ?');
if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) $destination = $res->fetch_assoc();
    $stmt->close();
}

if (!$destination) {
    http_response_code(404);
    echo '<div class="fragment-error">Destination introuvable.</div>';
    exit;
}

// images
$imageStmt = $conn->prepare('SELECT id, image_url, alt_text, is_primary, sort_order FROM destination_images WHERE destination_id = ? ORDER BY is_primary DESC, sort_order ASC');
if ($imageStmt) {
    $imageStmt->bind_param('i', $id);
    $imageStmt->execute();
    $ir = $imageStmt->get_result();
    if ($ir) {
        while ($row = $ir->fetch_assoc()) $destinationImages[] = $row;
    }
    $imageStmt->close();
}
if (empty($destinationImages) && !empty($destination['image'])) {
    $destinationImages[] = ['id'=>0,'image_url'=>$destination['image'],'alt_text'=>$destination['nom'],'is_primary'=>1,'sort_order'=>0];
}

// reviews limited
$reviewStmt = $conn->prepare('SELECT r.rating, r.comment, r.created_at, u.Nom_users FROM reviews r JOIN users u ON r.user_id = u.Id WHERE r.destination_id = ? ORDER BY r.created_at DESC LIMIT 3');
if ($reviewStmt) {
    $reviewStmt->bind_param('i', $id);
    $reviewStmt->execute();
    $rr = $reviewStmt->get_result();
    if ($rr) while ($row = $rr->fetch_assoc()) $reviews[] = $row;
    $reviewStmt->close();
}

if (!empty($reviews)) {
    $total = 0; foreach ($reviews as $r) $total += $r['rating'];
    $averageRating = round($total / count($reviews), 1);
}

// Build fragment
?>
<div class="detail-fragment">
    <div class="fragment-hero">
        <div class="fragment-carousel">
            <?php foreach ($destinationImages as $index => $img): ?>
                <div class="fragment-slide <?= $index === 0 ? 'active' : '' ?>">
                    <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="<?= htmlspecialchars($img['alt_text'] ?? $destination['nom']) ?>" loading="lazy">
                </div>
            <?php endforeach; ?>
        </div>
        <div class="fragment-hero-content">
            <h2><?= htmlspecialchars($destination['nom']) ?></h2>
            <p class="hero-country"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($destination['pays']) ?></p>
        </div>
    </div>

    <div class="fragment-body">
        <div class="fragment-description">
            <p><?= nl2br(htmlspecialchars(substr($destination['description'],0,1000))) ?></p>
        </div>
        <div class="fragment-price">
            <strong>Prix de base :</strong> <?= number_format($destination['prix'], 2) ?> $
        </div>

        <div class="fragment-actions" style="margin-top:14px;">
            <a href="reservations.php?id=<?= $destination['id'] ?>" class="btn btn-book-large">Réserver</a>
            <a href="details_destination.php?id=<?= $destination['id'] ?>" class="btn btn-view-full" style="margin-left:10px;">Ouvrir la fiche complète</a>
        </div>

        <div class="fragment-reviews" style="margin-top:18px;">
            <h4>Avis récents (<?= count($reviews) ?>)</h4>
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $rev): ?>
                    <div class="fragment-review">
                        <div class="rev-author"><?= htmlspecialchars($rev['Nom_users']) ?></div>
                        <div class="rev-rating"><?php for($i=1;$i<=5;$i++): ?><i class="fas fa-star <?= $i <= $rev['rating'] ? 'active' : '' ?>"></i><?php endfor; ?></div>
                        <div class="rev-comment"><?= nl2br(htmlspecialchars(substr($rev['comment'],0,400))) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-reviews">Aucun avis pour le moment.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
