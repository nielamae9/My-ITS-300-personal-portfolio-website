<?php

function save_contact_message(PDO $connection, array $message): int
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
    if (strlen($name) < 2 || strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || strlen($email) > 254 || $subject === '' || strlen($subject) > 180
        || strlen($body) < 10 || strlen($body) > 5000
    ) {
        throw new InvalidArgumentException('Check your name, email, subject, and message.');
    }

    $statement = $connection->prepare(
        'INSERT INTO contact_messages (name, email, subject, message)
         VALUES (:name, :email, :subject, :message)'
    );
    $statement->execute([
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $body,
    ]);

    return (int) $connection->lastInsertId();
}
