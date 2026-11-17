<?php

function validate_contact_message(array $message): array
{
    $name = $message['name'] ?? null;
    $email = $message['email'] ?? null;
    $subject = $message['subject'] ?? null;
    $body = $message['message'] ?? null;

    if (!is_string($name) || !is_string($email) || !is_string($subject) || !is_string($body)) {
        throw new InvalidArgumentException('Check your name, email, subject, and message.');
    }

    $name = trim($name);
    $email = trim($email);
    $subject = trim($subject);
    $body = trim($body);
    $nameLength = preg_match_all('/./us', $name);
    $subjectLength = preg_match_all('/./us', $subject);
    $bodyLength = preg_match_all('/./us', $body);
    if ($nameLength === false || $nameLength < 2 || $nameLength > 120
        || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254
        || $subjectLength === false || $subjectLength === 0 || $subjectLength > 180
        || $bodyLength === false || $bodyLength < 10 || $bodyLength > 5000
    ) {
        throw new InvalidArgumentException('Check your name, email, subject, and message.');
    }

    return ['name' => $name, 'email' => $email, 'subject' => $subject, 'message' => $body];
}

function store_contact_message(PDO $connection, array $message): int
{
    $statement = $connection->prepare(
        'INSERT INTO contact_messages (name, email, subject, message)
         VALUES (:name, :email, :subject, :message)'
    );
    $statement->execute([
        'name' => $message['name'],
        'email' => $message['email'],
        'subject' => $message['subject'],
        'message' => $message['message'],
    ]);

    return (int) $connection->lastInsertId();
}

function save_contact_message(PDO $connection, array $message): int
{
    return store_contact_message($connection, validate_contact_message($message));
}

function get_contact_messages(PDO $connection): array
{
    return $connection->query(
        'SELECT id, name, email, subject, message, status, created_at
         FROM contact_messages ORDER BY created_at DESC, id DESC LIMIT 100'
    )->fetchAll();
}

function set_contact_message_status(PDO $connection, string $messageId, string $status): bool
{
    $id = filter_var($messageId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false || !in_array($status, ['read', 'unread'], true)) {
        throw new InvalidArgumentException('Choose a valid message and status.');
    }

    $statement = $connection->prepare('UPDATE contact_messages SET status = :status WHERE id = :id');
    $statement->execute(['status' => $status, 'id' => $id]);
    if ($statement->rowCount() === 1) {
        return true;
    }

    $check = $connection->prepare('SELECT id FROM contact_messages WHERE id = :id');
    $check->execute(['id' => $id]);
    return $check->fetchColumn() !== false;
}

function delete_contact_message(PDO $connection, string $messageId): bool
{
    $id = filter_var($messageId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        throw new InvalidArgumentException('Choose a valid message.');
    }

    $statement = $connection->prepare('DELETE FROM contact_messages WHERE id = :id');
    $statement->execute(['id' => $id]);
    return $statement->rowCount() === 1;
}
