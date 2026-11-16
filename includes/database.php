<?php

function get_database_connection(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $configPath = __DIR__ . '/config.local.php';
    if (!is_file($configPath)) {
        throw new RuntimeException(
            'Database configuration is missing. Copy includes/config.example.php to includes/config.local.php and update its settings.'
        );
    }

    $config = require $configPath;
    if (!isset($config['database']) || !is_array($config['database'])) {
        throw new RuntimeException('The database configuration is invalid.');
    }

    $database = $config['database'];
    foreach (['host', 'port', 'name', 'username', 'password'] as $key) {
        if (!array_key_exists($key, $database)) {
            throw new RuntimeException('The database configuration is missing the "' . $key . '" setting.');
        }
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $database['host'],
        $database['port'],
        $database['name']
    );

    $connection = new PDO($dsn, $database['username'], $database['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $connection;
}
