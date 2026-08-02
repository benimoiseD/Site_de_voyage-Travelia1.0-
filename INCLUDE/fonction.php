<?php

/**
 * Configuration sécurisée de la session
 */
function secure_session_start(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        // Configuration des cookies de session
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $httponly = true;
        $samesite = 'Strict';

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite
        ]);

        session_start();

        // Régénérer l'ID de session pour prévenir la fixation de session
        if (!isset($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
        }
    }
}

/**
 * Retourne les initiales d'un nom.
 */
function dashboardInitials(string $name): string
{
    $words = preg_split('/\s+/', trim($name));
    $initials = '';

    foreach ($words as $word) {
        if (isset($word[0]) && mb_strlen($initials) < 2) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }
    }

    return $initials;
}

/**
 * CSRF helper utilities
 */
function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!isset($_SESSION['csrf_tokens']) || !is_array($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }

    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_tokens'][$token] = time();

    // prune tokens older than 2 hours
    $cutoff = time() - 60 * 60 * 2;
    foreach ($_SESSION['csrf_tokens'] as $t => $ts) {
        if ($ts < $cutoff) {
            unset($_SESSION['csrf_tokens'][$t]);
        }
    }

    return $token;
}

function csrf_input_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

function validate_csrf_token(string $token): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($token) || !isset($_SESSION['csrf_tokens'][$token])) {
        return false;
    }

    // single-use token
    unset($_SESSION['csrf_tokens'][$token]);
    return true;
}

/**
 * Validation et sanitisation des entrées
 */

/**
 * Sanitise une chaîne de caractères pour prévenir XSS
 */
function sanitize_string(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Valide un email
 */
function validate_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valide une date au format YYYY-MM-DD
 */
function validate_date(string $date): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Valide que la date est dans le futur
 */
function validate_future_date(string $date): bool
{
    if (!validate_date($date)) {
        return false;
    }
    $d = new DateTime($date);
    $now = new DateTime();
    return $d > $now;
}

/**
 * Valide un nombre entier positif
 */
function validate_positive_int($value): bool
{
    return filter_var($value, FILTER_VALIDATE_INT) !== false && $value > 0;
}

/**
 * Valide un nombre décimal positif
 */
function validate_positive_float($value): bool
{
    return filter_var($value, FILTER_VALIDATE_FLOAT) !== false && $value > 0;
}

/**
 * Valide la longueur d'une chaîne
 */
function validate_length(string $input, int $min, int $max): bool
{
    $length = mb_strlen(trim($input));
    return $length >= $min && $length <= $max;
}

/**
 * Valide qu'une valeur fait partie d'une liste autorisée
 */
function validate_enum(string $input, array $allowed): bool
{
    return in_array($input, $allowed, true);
}

/**
 * Récupère l'email d'un utilisateur connecté via son ID
 */
function get_user_email_by_id(mysqli $conn, int $userId): string
{
    $stmt = $conn->prepare('SELECT Email FROM users WHERE Id = ?');
    if (!$stmt) {
        return '';
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();

    return $email ?: '';
}

/**
 * Récupère le pays de résidence d'un utilisateur via son ID.
 */
function get_user_country_by_id(mysqli $conn, int $userId): string
{
    $stmt = $conn->prepare('SELECT pays_residence FROM users WHERE Id = ?');
    if (!$stmt) {
        return '';
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($country);
    $stmt->fetch();
    $stmt->close();

    return is_string($country) ? trim($country) : '';
}

/**
 * Valide un mot de passe (minimum 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre)
 */
function validate_password(string $password): bool
{
    if (strlen($password) < 8) {
        return false;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    return true;
}

/**
 * Valide un nom (lettres, espaces, tirets, apostrophes)
 */
function validate_name(string $name): bool
{
    return preg_match('/^[a-zA-Zàáâäãåąčćęèéêëėįìíîïłńòóôöõøùúûüųūÿýżźñç\s\-\'\.]+$/u', $name) === 1;
}

/**
 * Retourne un message d'erreur générique pour les échecs SQL.
 */
function sql_error_message(string $action = 'exécuter la requête'): string
{
    return 'Erreur interne de base de données : impossible de ' . $action . '.';
}

/**
 * Retourne un message lisible pour un code d'erreur d'upload.
 */
function upload_error_message(int $errorCode): string
{
    switch ($errorCode) {
        case UPLOAD_ERR_OK:
            return 'Aucune erreur de téléchargement.';
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'La photo doit faire moins de 2 Mo.';
        case UPLOAD_ERR_PARTIAL:
            return 'Le fichier a été partiellement téléchargé. Veuillez réessayer.';
        case UPLOAD_ERR_NO_FILE:
            return 'Aucun fichier n’a été téléchargé.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Dossier temporaire manquant sur le serveur.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Impossible d’écrire le fichier sur le serveur.';
        case UPLOAD_ERR_EXTENSION:
            return 'Une extension du serveur a empêché le téléchargement.';
        default:
            return 'Erreur lors du téléversement du fichier.';
    }
}

/**
 * Valide un numéro de téléphone
 */
function validate_phone(string $phone): bool
{
    return preg_match('/^[\d\+\-\s\(\)]{10,20}$/', $phone) === 1;
}

/**
 * Rate limiting pour la connexion
 */

/**
 * Vérifie si l'utilisateur peut tenter de se connecter
 * @param mysqli $conn Connexion MySQLi
 * @param string $email Email de l'utilisateur
 * @param int $maxAttempts Nombre maximum de tentatives
 * @param int $windowSeconds Fenêtre de temps en secondes
 * @return bool true si autorisé, false si bloqué
 */
function can_attempt_login(mysqli $conn, string $email, int $maxAttempts = 5, int $windowSeconds = 900): bool
{
    $email = mb_strtolower(trim($email));
    if ($email === '') {
        return false;
    }

    $stmt = $conn->prepare('SELECT attempts, window_start FROM login_attempts WHERE email = ?');
    if (!$stmt) {
        return true;
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($attempts, $windowStart);
    $rowFound = $stmt->fetch();
    $stmt->close();

    if (!$rowFound) {
        return true;
    }

    $attempts = (int)$attempts;
    $windowStart = (int)$windowStart;
    if (time() - $windowStart > $windowSeconds) {
        return true;
    }

    return $attempts < $maxAttempts;
}

/**
 * Enregistre une tentative de connexion échouée
 * @param mysqli $conn Connexion MySQLi
 * @param string $email Email de l'utilisateur
 * @param int $windowSeconds Fenêtre de temps en secondes
 * @return int Nombre de tentatives restantes
 */
function record_failed_login_attempt(mysqli $conn, string $email, int $windowSeconds = 900): int
{
    $email = mb_strtolower(trim($email));
    if ($email === '') {
        return 0;
    }

    $now = time();
    $stmt = $conn->prepare('SELECT attempts, window_start FROM login_attempts WHERE email = ?');
    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($attempts, $windowStart);
    $rowFound = $stmt->fetch();
    $stmt->close();

    if (!$rowFound) {
        $attempts = 1;
        $windowStart = $now;
        $insert = $conn->prepare('INSERT INTO login_attempts (email, attempts, window_start, last_attempt) VALUES (?, ?, ?, NOW())');
        if ($insert) {
            $insert->bind_param('sii', $email, $attempts, $windowStart);
            $insert->execute();
            $insert->close();
        }
        return max(0, 5 - $attempts);
    }

    $attempts = (int)$attempts;
    $windowStart = (int)$windowStart;

    if ($now - $windowStart > $windowSeconds) {
        $attempts = 1;
        $windowStart = $now;
        $update = $conn->prepare('UPDATE login_attempts SET attempts = ?, window_start = ?, last_attempt = NOW() WHERE email = ?');
        if ($update) {
            $update->bind_param('iis', $attempts, $windowStart, $email);
            $update->execute();
            $update->close();
        }
        return max(0, 5 - $attempts);
    }

    $attempts++;
    $update = $conn->prepare('UPDATE login_attempts SET attempts = ?, last_attempt = NOW() WHERE email = ?');
    if ($update) {
        $update->bind_param('is', $attempts, $email);
        $update->execute();
        $update->close();
    }

    return max(0, 5 - $attempts);
}

/**
 * Réinitialise les tentatives de connexion après une connexion réussie
 * @param mysqli $conn Connexion MySQLi
 * @param string $email Email de l'utilisateur
 */
function reset_login_attempts(mysqli $conn, string $email): void
{
    $email = mb_strtolower(trim($email));
    if ($email === '') {
        return;
    }

    $stmt = $conn->prepare('DELETE FROM login_attempts WHERE email = ?');
    if ($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Obtient le temps d'attente restant avant la prochaine tentative
 * @param mysqli $conn Connexion MySQLi
 * @param string $email Email de l'utilisateur
 * @param int $windowSeconds Fenêtre de temps en secondes
 * @return int Temps d'attente en secondes (0 si pas bloqué)
 */
function get_login_wait_time(mysqli $conn, string $email, int $windowSeconds = 900): int
{
    $email = mb_strtolower(trim($email));
    if ($email === '') {
        return 0;
    }

    $stmt = $conn->prepare('SELECT attempts, window_start FROM login_attempts WHERE email = ?');
    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($attempts, $windowStart);
    $rowFound = $stmt->fetch();
    $stmt->close();

    if (!$rowFound) {
        return 0;
    }

    $attempts = (int)$attempts;
    $windowStart = (int)$windowStart;

    if ($attempts < 5) {
        return 0;
    }

    $elapsed = time() - $windowStart;
    $remaining = $windowSeconds - $elapsed;

    return max(0, $remaining);
}
