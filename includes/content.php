<?php

function get_portfolio_items(PDO $connection, string $section): array
{
    $tables = [
        'skills' => 'skills',
        'projects' => 'projects',
        'education' => 'education',
        'experience' => 'experience',
        'achievements' => 'achievements',
    ];

    if (!isset($tables[$section])) {
        throw new InvalidArgumentException('Unknown portfolio section.');
    }

    $table = $tables[$section];
    return $connection->query(
        'SELECT * FROM `' . $table . '` ORDER BY sort_order, id'
    )->fetchAll();
}
