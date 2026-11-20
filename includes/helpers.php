<?php
// Shared helper functions. Included at the top of every page.
session_start();

// Escape output so user input cannot run as HTML/JS (XSS protection)
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit; // always stop the script after a redirect
}

// Predefined sample credentials (no database for this exercise)
// Email: ali@example.com   Password: Password@123
function sample_users(): array
{
    return [
        'ali@example.com' => [
            'name'          => 'Ali Khan',
            'password_hash' => password_hash('Password@123', PASSWORD_DEFAULT),
        ],
    ];
}

// Flash messages: shown once, then removed
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// CSRF token to make sure the form came from our own site
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valid(?string $token): bool
{
    return isset($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}
