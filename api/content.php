<?php

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/content.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    exit;
}

$section = $_GET['section'] ?? '';
$allowedSections = ['skills', 'projects', 'education', 'experience', 'achievements'];
if (!is_string($section) || !in_array($section, $allowedSections, true)) {
    http_response_code(400);
    exit(json_encode(['error' => 'Choose a valid content section.']));
}

try {
    $connection = get_database_connection();
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
