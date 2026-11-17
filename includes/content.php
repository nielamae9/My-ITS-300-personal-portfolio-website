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

function create_skill(PDO $connection, array $skill): int
{
    $name = $skill['name'] ?? null;
    $category = $skill['category'] ?? null;
    if (!is_string($name) || !is_string($category) || trim($name) === '' || strlen($name) > 400 || trim($category) === '' || strlen($category) > 400) {
        throw new InvalidArgumentException('Enter a skill name and category.');
    }

    $input = $skill['proficiency'] ?? '';
    if ($input !== '' && !is_string($input) && !is_int($input)) {
        throw new InvalidArgumentException('Skill proficiency must be a whole number.');
    }
    $proficiency = $input === '' ? null : filter_var($input, FILTER_VALIDATE_INT);
    if ($proficiency !== null && ($proficiency === false || $proficiency < 0 || $proficiency > 100)) {
        throw new InvalidArgumentException('Skill proficiency must be between 0 and 100.');
    }

    $statement = $connection->prepare('INSERT INTO skills (name, category, proficiency) VALUES (:name, :category, :proficiency)');
    $statement->execute(['name' => trim($name), 'category' => trim($category), 'proficiency' => $proficiency]);
    return (int) $connection->lastInsertId();
}
