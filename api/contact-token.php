<?php

require_once __DIR__ . '/../includes/security.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    exit;
}

start_contact_session();
echo json_encode(['csrf_token' => get_csrf_token()]);
