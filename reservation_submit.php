<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/BASE_DES_DONNEES/db.php';
require_once __DIR__ . '/INCLUDE/fonction.php';

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

$destination = trim($_POST['destination'] ?? '');
$date = trim($_POST['date'] ?? '');
$guests = trim($_POST['guests'] ?? '');
$type = trim($_POST['type'] ?? '');
$email = trim($_POST['email'] ?? '');

if (empty($destination) || empty($date) || empty($guests) || empty($type) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires et doivent être valides.']);
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
