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

if (!function_exists('dashboardInitials')) {
    function dashboardInitials($name) {
        $words = explode(' ', trim($name));
        $initials = '';

        foreach ($words as $word) {
            if (isset($word[0]) && strlen($initials) < 2) {
                $initials .= strtoupper($word[0]);
            }
        }

        return $initials;
    }
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
 * @param string $email Email de l'utilisateur
 * @param int $maxAttempts Nombre maximum de tentatives
 * @param int $windowSeconds Fenêtre de temps en secondes
 * @return bool true si autorisé, false si bloqué
 */
function can_attempt_login(string $email, int $maxAttempts = 5, int $windowSeconds = 900): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $key = 'login_attempts_' . md5($email);
    $now = time();

    if (!isset($_SESSION[$key])) {
        return true;
    }

    $attempts = $_SESSION[$key];
    $windowStart = $attempts['window_start'] ?? $now;
    $count = $attempts['count'] ?? 0;

    // Réinitialiser si la fenêtre est expirée
    if ($now - $windowStart > $windowSeconds) {
        unset($_SESSION[$key]);
        return true;
    }

    // Vérifier si le nombre maximum est atteint
    return $count < $maxAttempts;
}

/**
 * Enregistre une tentative de connexion échouée
 * @param string $email Email de l'utilisateur
 * @param int $windowSeconds Fenêtre de temps en secondes
 * @return int Nombre de tentatives restantes
 */
function record_failed_login_attempt(string $email, int $windowSeconds = 900): int
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $key = 'login_attempts_' . md5($email);
    $now = time();

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'window_start' => $now,
            'count' => 1
        ];
        return 4; // 5 - 1
    }

    $attempts = $_SESSION[$key];
    $windowStart = $attempts['window_start'] ?? $now;
    $count = $attempts['count'] ?? 0;

    // Réinitialiser si la fenêtre est expirée
    if ($now - $windowStart > $windowSeconds) {
        $_SESSION[$key] = [
            'window_start' => $now,
            'count' => 1
        ];
        return 4;
    }

    // Incrémenter le compteur
    $_SESSION[$key]['count'] = $count + 1;
    return max(0, 5 - ($count + 1));
}

/**
 * Réinitialise les tentatives de connexion après une connexion réussie
 * @param string $email Email de l'utilisateur
 */
function reset_login_attempts(string $email): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $key = 'login_attempts_' . md5($email);
    unset($_SESSION[$key]);
}

/**
 * Obtient le temps d'attente restant avant la prochaine tentative
 * @param string $email Email de l'utilisateur
 * @param int $windowSeconds Fenêtre de temps en secondes
 * @return int Temps d'attente en secondes (0 si pas bloqué)
 */
function get_login_wait_time(string $email, int $windowSeconds = 900): int
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $key = 'login_attempts_' . md5($email);

    if (!isset($_SESSION[$key])) {
        return 0;
    }

    $attempts = $_SESSION[$key];
    $windowStart = $attempts['window_start'] ?? time();
    $count = $attempts['count'] ?? 0;

    if ($count < 5) {
        return 0;
    }

    $now = time();
    $elapsed = $now - $windowStart;
    $remaining = $windowSeconds - $elapsed;

    return max(0, $remaining);
}