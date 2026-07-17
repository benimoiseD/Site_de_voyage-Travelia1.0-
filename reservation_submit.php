<?php
require_once __DIR__ . '/INCLUDE/fonction.php';
secure_session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/BASE_DES_DONNEES/db.php';

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

// validate CSRF token (accept POST field or X-CSRF-Token header)
$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validate_csrf_token((string)$csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Jeton CSRF invalide.']);
    exit;
}

$destination = sanitize_string($_POST['destination'] ?? '');
$date = sanitize_string($_POST['date'] ?? '');
$guests = $_POST['guests'] ?? '';
$type = sanitize_string($_POST['type'] ?? '');
$email = sanitize_string($_POST['email'] ?? '');

if (empty($destination) || empty($date) || empty($guests) || empty($type) || empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires.']);
    exit;
}

if (!validate_length($destination, 2, 255)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le nom de la destination est invalide.']);
    exit;
}

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

$validTypes = ['solo', 'couple', 'famille', 'groupe', 'affaires'];
if (!in_array($type, $validTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le type de séjour est invalide.']);
    exit;
}

if (!validate_email($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Adresse email invalide.']);
    exit;
}

$userId = $_SESSION['Id'];
$userEmail = $_SESSION['email'] ?? $email;

$insert = $conn->prepare(
    'INSERT INTO reservations (user_id, destination, date_depart, guests, type_sejour, email, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)' 
);
if (!$insert) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur interne de base de données.']);
    exit;
}

$createdAt = date('Y-m-d H:i:s');
$insert->bind_param('ississs', $userId, $destination, $date, $guests, $type, $userEmail, $createdAt);

if ($insert->execute()) {
    echo json_encode(['success' => true, 'message' => 'Réservation enregistrée avec succès.']);
    exit;
}

http_response_code(500);
echo json_encode(['success' => false, 'message' => 'Impossible de sauvegarder la réservation.']);
