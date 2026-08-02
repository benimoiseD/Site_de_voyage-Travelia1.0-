<?php
require_once __DIR__ . '/INCLUDE/fonction.php';
secure_session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/INCLUDE/db.php';

$conn = get_db_connection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

if (!isset($_SESSION['Id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour effectuer une réservation.']);
    exit;
}

$userId = $_SESSION['Id'];
$userEmail = $_SESSION['email'] ?? '';

if ($userEmail === '') {
    $userEmail = get_user_email_by_id($conn, $userId);
}

// validate CSRF token (accept POST field or X-CSRF-Token header)
$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validate_csrf_token((string)$csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Jeton CSRF invalide.']);
    exit;
}

$destinationId = $_POST['destination_id'] ?? '';
$date = sanitize_string($_POST['date'] ?? '');
$guests = $_POST['guests'] ?? '';
$type = sanitize_string($_POST['type'] ?? '');
$notes = trim((string)($_POST['notes'] ?? ''));

if (empty($destinationId) || empty($date) || empty($guests) || empty($type) || empty($userEmail)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires.']);
    exit;
}

if (!validate_positive_int($destinationId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Identifiant de destination invalide.']);
    exit;
}

$destinationId = (int)$destinationId;
$destinationName = '';
$destinationStmt = $conn->prepare('SELECT id, nom FROM destinations WHERE id = ?');
if (!$destinationStmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur interne de base de données.']);
    exit;
}
$destinationStmt->bind_param('i', $destinationId);
$destinationStmt->execute();
$destinationResult = $destinationStmt->get_result();
if (!$destinationResult || $destinationResult->num_rows !== 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Destination introuvable.']);
    exit;
}
$destinationRow = $destinationResult->fetch_assoc();
$destinationName = $destinationRow['nom'];
$destinationStmt->close();

if (!validate_date($date)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'La date de départ est invalide (format YYYY-MM-DD requis).']);
    exit;
}

if (!validate_future_date($date)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'La date de départ doit être dans le futur.']);
    exit;
}

if (!validate_positive_int($guests)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le nombre de personnes doit être un entier positif.']);
    exit;
}

if ($guests > 50) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le nombre maximum de personnes est de 50.']);
    exit;
}

$validTypes = ['individuel', 'couple', 'groupe', 'lune_de_miel', 'famille', 'aventure'];
if (!validate_enum($type, $validTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le type de séjour est invalide.']);
    exit;
}

if ($notes !== '' && !validate_length($notes, 0, 1000)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Les notes supplémentaires sont trop longues.']);
    exit;
}

if (!validate_email($userEmail)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Adresse email invalide.']);
    exit;
}

$userId = $_SESSION['Id'];

$insert = $conn->prepare(
    'INSERT INTO reservations (user_id, destination, date_depart, guests, type_sejour, email, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)' 
);
if (!$insert) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur interne de base de données.']);
    exit;
}

$createdAt = date('Y-m-d H:i:s');
$notesValue = $notes !== '' ? $notes : null;
$insert->bind_param('ississss', $userId, $destinationName, $date, $guests, $type, $userEmail, $notesValue, $createdAt);

if ($insert->execute()) {
    echo json_encode(['success' => true, 'message' => 'Réservation enregistrée avec succès.']);
    exit;
}

http_response_code(500);
echo json_encode(['success' => false, 'message' => 'Impossible de sauvegarder la réservation.']);
