<?php

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/content.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    exit;
}

$section = $_GET['section'] ?? '';
$allowedSections = ['profile', 'settings', 'skills', 'projects', 'education', 'experience', 'achievements'];
if (!is_string($section) || !in_array($section, $allowedSections, true)) {
    http_response_code(400);
    exit(json_encode(['error' => 'Choose a valid content section.']));
}

try {
    $connection = get_database_connection();
    if ($section === 'profile') {
        $profile = get_portfolio_profile($connection);
        if (!$profile) {
            http_response_code(404);
            exit(json_encode(['error' => 'Portfolio profile was not found.']));
        }
        echo json_encode(['profile' => $profile], JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }
    if ($section === 'settings') {
        echo json_encode(['settings' => get_portfolio_settings($connection)], JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }
    echo json_encode(['items' => get_portfolio_items($connection, $section)], JSON_INVALID_UTF8_SUBSTITUTE);
} catch (PDOException $error) {
    error_log('Portfolio content API database error: ' . $error->getMessage());
    http_response_code(503);
    exit(json_encode(['error' => 'Portfolio content is temporarily unavailable.']));
} catch (RuntimeException $error) {
    error_log('Portfolio content API setup error: ' . $error->getMessage());
    http_response_code(503);
    exit(json_encode(['error' => 'Portfolio content is temporarily unavailable.']));
}
