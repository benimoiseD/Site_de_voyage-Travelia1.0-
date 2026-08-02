<?php
require_once __DIR__ . '/INCLUDE/fonction.php';
secure_session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/INCLUDE/db.php';

$conn = get_db_connection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

if (!isset($_SESSION['Id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour laisser un avis.']);
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validate_csrf_token((string)$csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Jeton CSRF invalide.']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$destinationId = $_POST['destination_id'] ?? '';
$rating = $_POST['rating'] ?? '';
$comment = trim((string)($_POST['comment'] ?? ''));

if (empty($destinationId) || empty($rating) || $comment === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires.']);
    exit;
}

if (!validate_positive_int($destinationId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de destination invalide.']);
    exit;
}

$destinationId = (int)$destinationId;
$destinationExistsStmt = $conn->prepare('SELECT id FROM destinations WHERE id = ?');
if (!$destinationExistsStmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur interne de base de données.']);
    exit;
}
$destinationExistsStmt->bind_param('i', $destinationId);
$destinationExistsStmt->execute();
$destinationExistsStmt->store_result();
if ($destinationExistsStmt->num_rows !== 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Destination introuvable.']);
    exit;
}
$destinationExistsStmt->close();

if (!validate_positive_int($rating) || $rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'La note doit être entre 1 et 5.']);
    exit;
}

if (!validate_length($comment, 10, 1000)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le commentaire doit contenir entre 10 et 1000 caractères.']);
    exit;
}

$userId = $_SESSION['Id'];

// Vérifier si l'utilisateur a déjà noté cette destination
$checkStmt = $conn->prepare('SELECT id FROM reviews WHERE user_id = ? AND destination_id = ?');
if ($checkStmt) {
    $checkStmt->bind_param('ii', $userId, $destinationId);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vous avez déjà laissé un avis pour cette destination.']);
        exit;
    }
}

$insert = $conn->prepare('INSERT INTO reviews (user_id, destination_id, rating, comment) VALUES (?, ?, ?, ?)');
if (!$insert) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur interne de base de données.']);
    exit;
}

$insert->bind_param('iiis', $userId, $destinationId, $rating, $comment);

if ($insert->execute()) {
    $insert->close();

    $statsStmt = $conn->prepare('SELECT COUNT(*) AS total_reviews, AVG(rating) AS average_rating FROM reviews WHERE destination_id = ?');
    $totalReviews = 0;
    $averageRating = 0;
    if ($statsStmt) {
        $statsStmt->bind_param('i', $destinationId);
        $statsStmt->execute();
        $statsStmt->bind_result($totalReviews, $averageRating);
        $statsStmt->fetch();
        $statsStmt->close();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Avis enregistré avec succès.',
        'review' => [
            'rating' => $rating,
            'comment' => $comment,
            'created_at' => date('d/m/Y'),
            'user_name' => $_SESSION['Nom_users'] ?? 'Anonyme'
        ],
        'review_count' => (int)$totalReviews,
        'average_rating' => $averageRating !== null ? round($averageRating, 1) : 0
    ]);
    exit;
}

http_response_code(500);
echo json_encode(['success' => false, 'message' => 'Impossible de sauvegarder l\'avis.']);
