<?php

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/messages.php';
require_once __DIR__ . '/../includes/security.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

start_contact_session();
if (!is_valid_csrf_token($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit(json_encode(['error' => 'Reload the contact form and try again.']));
}
$website = $_POST['website'] ?? '';
if (!is_string($website) || trim($website) !== '') {
    http_response_code(400);
    exit(json_encode(['error' => 'The message could not be accepted.']));
}
$lastSubmission = $_SESSION['last_contact_submission'] ?? null;
if (is_int($lastSubmission) && time() - $lastSubmission < 30) {
    http_response_code(429);
    exit(json_encode(['error' => 'Please wait before sending another message.']));
}

try {
    $message = validate_contact_message($_POST);
    $connection = get_database_connection();
    store_contact_message($connection, $message);
    $_SESSION['last_contact_submission'] = time();
    echo json_encode(['message' => 'Your message has been sent.']);
} catch (InvalidArgumentException $error) {
    http_response_code(422);
    exit(json_encode(['error' => $error->getMessage()]));
} catch (PDOException $error) {
    error_log('Contact message database error: ' . $error->getMessage());
    http_response_code(503);
    exit(json_encode(['error' => 'Messages are temporarily unavailable.']));
} catch (RuntimeException $error) {
    error_log('Contact message setup error: ' . $error->getMessage());
    http_response_code(503);
    exit(json_encode(['error' => 'Messages are temporarily unavailable.']));
}
