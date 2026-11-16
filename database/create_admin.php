<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../includes/database.php';

if ($argc !== 2 || !filter_var($argv[1], FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php database/create_admin.php admin@example.com\n");
    exit(1);
}

$email = strtolower($argv[1]);
$connection = get_database_connection();
$adminCount = (int) $connection->query('SELECT COUNT(*) FROM admins')->fetchColumn();

if ($adminCount > 0) {
    fwrite(STDERR, "An administrator already exists. No account was created.\n");
    exit(1);
}

$password = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$statement = $connection->prepare(
    'INSERT INTO admins (email, password_hash) VALUES (:email, :password_hash)'
);
$statement->execute([
    'email' => $email,
    'password_hash' => $passwordHash,
]);

fwrite(STDOUT, "Administrator account created.\n");
fwrite(STDOUT, 'Email: ' . $email . "\n");
fwrite(STDOUT, 'Password (shown once): ' . $password . "\n");
fwrite(STDOUT, "Save the password somewhere private. It is not stored in plain text.\n");
