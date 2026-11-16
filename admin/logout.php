<?php

require_once __DIR__ . '/../includes/security.php';

start_admin_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Use the logout form to end your session.');
}

if (!is_valid_csrf_token($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('Your form expired. Reload the dashboard and try again.');
}

end_admin_session();

header('Location: login.php');
exit;
