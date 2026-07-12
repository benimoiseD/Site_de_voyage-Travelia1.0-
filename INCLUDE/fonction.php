<?php

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