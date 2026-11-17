<?php

function start_admin_session(): void
{
    start_secure_session('portfolio_admin');
}

function start_contact_session(): void
{
    start_secure_session('portfolio_contact');
}

function start_secure_session(string $name): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name($name);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function get_csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function is_valid_csrf_token($token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function escape_html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function safe_external_url(string $url): string
{
    $scheme = parse_url($url, PHP_URL_SCHEME);

    if (!filter_var($url, FILTER_VALIDATE_URL)
        || !is_string($scheme)
        || !in_array(strtolower($scheme), ['http', 'https'], true)
    ) {
        return '';
    }

    return $url;
}

function end_admin_session(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $cookie = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $cookie['path'],
            'domain' => $cookie['domain'],
            'secure' => $cookie['secure'],
            'httponly' => $cookie['httponly'],
            'samesite' => $cookie['samesite'] ?? 'Lax',
        ]);
    }

    session_destroy();
}

function require_admin(): void
{
    start_admin_session();

    if (!isset($_SESSION['admin_id']) || !is_int($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }

    if (!isset($_SESSION['last_activity'])
        || !is_int($_SESSION['last_activity'])
        || time() - $_SESSION['last_activity'] > 1800
    ) {
        end_admin_session();
        header('Location: login.php?expired=1');
        exit;
    }

    $_SESSION['last_activity'] = time();
}
